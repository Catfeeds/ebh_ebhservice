<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 10:13
 */
class SmsUtil{
    public $client = null;

    public function __construct(){
        include "aliyun_dayu/TopSdk.php";
        date_default_timezone_set('Asia/Shanghai');
        $this->client = new TopClient;
        $this->client->format = 'json';
        $this->client->appkey = Ebh()->config->get('sms.alidayu.appkey');
        $this->client->secretKey = Ebh()->config->get('sms.alidayu.secretKey');
    }

    /**
     * 发送签到通知
     * @param $mobile
     * @param $coursename
     * @param $time
     * @return bool
     */
    public function sendCheckinNotify($mobile,$coursename,$time,$code){
        if(!is_array($mobile)){
            return false;
        }
        $name = '';
        $time = date('m月d日 H:i',$time);
        $mobile = implode(',',$mobile);
        $request = new AlibabaAliqinFcSmsNumSendRequest;
        $request->setSmsType('normal');
        $request->setSmsFreeSignName('网络学校');
        //$request->setSmsParam("{\"name\":\"".$name."\",\"time\":\"".$time."\",\"coursename\":\"".$coursename."\"}");
        $request->setSmsParam("{\"code\":\"".$code."\",\"time\":\"".$time."\",\"coursename\":\"".$coursename."\"}");
        $request->setRecNum($mobile);
        $request->setSmsTemplateCode('SMS_116445058');
        $result =  $this->client->execute($request);
        if(isset($result->result) && $result->result->err_code == 0){
            //设置验证码缓存 验证只存在半小时
            //Ebh()->cache->set('safety_captcha_'.$mobile,$code,1800);
            return true;
        }
        return false;
        //return $result;
    }
}