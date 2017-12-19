<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:57
 * 微信支付类库
 */
require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';
class Wxpay implements Payment{
    private $config = array();
    public function __construct($config){
        $this->config = $config;

        WxPayConfig::setConfig($this->config);

    }

    /**
     * 生成支付代码
     * @param $order
     */
    public function getPaymentCode($order,$parameters = array()){
        if(!isset($parameters['code'])){
            throw new Exception_BadRequest('缺少必要附加参数 code');
        }

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$this->config['appid'].'&secret='.$this->config['appsecret'].'&js_code='.$parameters['code'].'&grant_type=authorization_code';
        $result = $this->getSslPage($url);
        $result = json_decode($result,true);
        if(!isset($result['openid'])){
            throw new Exception_InternalServerError('获取微信登录信息失败');
        }
        $input = new WxPayUnifiedOrder();
        $input->SetBody('课程购买');
        $input->SetOut_trade_no($order['orderid']);
        $input->SetTotal_fee($order['totalfee'] * 100);
        //$input->SetTotal_fee(1);
        $input->SetSpbill_create_ip(getip());
        $input->SetTrade_type("JSAPI");

        //$input->SetOpenid('oX-_u0ClpQJhAYZIT_N5S1-vlUT8');//alert
        //$input->SetOpenid('oX-_u0NACSGTAEFh2xOZw1H68N5g');
        $input->SetOpenid($result['openid']);
        $order = WxPayApi::unifiedOrder($input);

        if($order['return_code'] == 'FAIL'){
            log_message('生成微信支付订单失败:'.json_encode($order));
            throw new Exception_InternalServerError('生成支付订单失败');
        }

        $jsapi = new WxPayJsApiPay();

        $jsapi->SetAppid($order["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $order['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign());
        $parameters = json_encode($jsapi->GetValues());

        return json_decode($parameters,true);

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

    public function notify($request = array()){
        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
    }
}

/**
 * 重写支付回调
 * Class PayNotifyCallBack
 */
class PayNotifyCallBack extends WxPayNotify{
    public function Queryorder($transaction_id){
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);

        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }
    //重写回调处理函数
    public function NotifyProcess($data, &$msg){
        log_message('call back'.json_encode($data));
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }

        //处理订单回调
        Transaction::notifyOrder($data['out_trade_no'],$data['transaction_id']);
        return true;
    }
}