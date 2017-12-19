<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:19
 */
class Wxapp implements OtherLoginInterface{
    public $openId = '';
    public $parameters = array();
    public $config = array();
    public $errMsg = '';
    public function __construct($openId,$parameters){
        $this->openId = $openId;
        $this->parameters = $parameters;
        //读取微信小程序配置
        $this->config = Ebh()->config->get('otherlogin.wxapp');
    }

    /**
     * 获取微信用户信息
     * @return mixed
     * @throws Exception_InternalServerError
     */
    public function getWxUserInfo(){

        if(!isset($this->parameters['encryptedData']) || !isset($this->parameters['iv'])){
            $this->errMsg = '缺少必要附加参数';
            return false;
        }
        $appid = $this->config['appid'];
        $appsecret = $this->config['appsecret'];

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$appsecret.'&js_code='.$this->openId.'&grant_type=authorization_code';

        $result = $this->getSslPage($url);
        $result = json_decode($result,true);
        if(!isset($result['openid'])){
            $this->errMsg = '获取微信登录信息失败';
            return false;
        }
        $sessionKey = $result['session_key'];
        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($this->parameters['encryptedData'], $this->parameters['iv'], $data );

        if ($errCode == 0) {
            return json_decode($data,true);
        } else {
            $this->errMsg = '解密微信信息失败,errcode:\'.$errCode';
            return false;
        }



    }
	function getSslPage($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
    /**
     * 获取用户信息
     */
    public function getUser(){
        $wxUser = $this->getWxUserInfo();
        if(!$wxUser){
            return false;
        }
        $userModel = new UserModel();
        $user = $userModel->openlogin($wxUser['unionId'],'wx');
        if(!$user){
            $this->errMsg = '用户未绑定';
            return false;
        }
        return $user;
    }


    public function bindUser($uid){
        $wxUser = $this->getWxUserInfo();
        //$wxUser = '{"openId":"oX-_u0ClpQJhAYZIT_N5S1-vlUT8","nickName":"alert","gender":1,"language":"zh_CN","city":"Wenzhou","province":"Zhejiang","country":"CN","avatarUrl":"http:\/\/wx.qlogo.cn\/mmopen\/vi_32\/DYAIOgq83eqmlM4ffzQmW8UeN4COibS6o1tCeTkSdbzK9II3xA6esS1lyafVPbnS5ibCgdZCB5pb7UEZibwe6Kwuw\/0","unionId":"oD5qxs_HbZlzRC00wBtZcGd83x5k","watermark":{"timestamp":1497494790,"appid":"wxd31461195ec7ef3a"}}';
        //$wxUser = json_decode($wxUser,true);

        if(!$wxUser){
            return false;
        }
        $userArr = array(
            'wx'    =>  '',
            'openid'    =>  $wxUser['openId'],
            'unionid'   =>  $wxUser['unionId'],
            'nickname'  =>  $wxUser['nickName'],
            'dateline'  =>  time(),
        );
        $userModel = new UserModel();
        $bindModel = new BindModel();
        $openModel = new OpenModel();
        $user = $userModel->getUserByWxOpenid($wxUser['unionId']);
        $bindUser = $userModel->getUserByUid($uid);
        if(!empty($bindUser['wxunionid'])){//自己已经绑定过了
            if(!empty($user)){
                //微信已经绑定过帐号
                if($user['uid'] == $uid){
                    return true;
                }
                //如果绑定过但是uid不同 先解绑 在绑定
                //解绑新用户
                $bindModel->doUnbind('wx',$uid);
                //解绑旧用户
                $bindModel->doUnbind('wx',$user['uid']);
                //绑定微信用户
                $openModel->dobind('wx',$userArr,$bindUser);

            }else{
                //解绑新用户
                $bindModel->doUnbind('wx',$uid);
                //绑定微信用户
                $openModel->dobind('wx',$userArr,$bindUser);
            }



        }else{
            if(!empty($user)){
                //解绑旧用户
                $bindModel->doUnbind('wx',$user['uid']);
            }

            //绑定新用户
            $openModel->dobind('wx',$userArr,$bindUser);
        }

        return true;



    }

    public function getErr(){
        return $this->errMsg;
    }
}


/**
 * error code 说明.
 * <ul>

 *    <li>-41001: encodingAesKey 非法</li>
 *    <li>-41003: aes 解密失败</li>
 *    <li>-41004: 解密后得到的buffer非法</li>
 *    <li>-41005: base64加密失败</li>
 *    <li>-41016: base64解密失败</li>
 * </ul>
 */
class ErrorCode{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder{
    public static $block_size = 16;

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode( $text ){
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen( $text );
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ( $text_length % PKCS7Encoder::$block_size );
        if ( $amount_to_pad == 0 ) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr( $amount_to_pad );
        $tmp = "";
        for ( $index = 0; $index < $amount_to_pad; $index++ ) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text){

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

}

/**
 * Prpcrypt class
 *
 *
 */
class Prpcrypt{
    public $key;

    function Prpcrypt( $k ){
        $this->key = $k;
    }

    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt( $aesCipher, $aesIV ){

        try {

            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

            mcrypt_generic_init($module, $this->key, $aesIV);

            //解密
            $decrypted = mdecrypt_generic($module, $aesCipher);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }


        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);

        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        return array(0, $result);
    }
}


class WXBizDataCrypt{
    private $appid;
    private $sessionKey;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function WXBizDataCrypt( $appid, $sessionKey){
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $encryptedData, $iv, &$data ){
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->sessionKey);


        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $pc = new Prpcrypt($aesKey);
        $result = $pc->decrypt($aesCipher,$aesIV);

        if ($result[0] != 0) {
            return $result[0];
        }

        $dataObj=json_decode( $result[1] );
        if( $dataObj  == NULL )
        {
            return ErrorCode::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result[1];
        return ErrorCode::$OK;
    }

}