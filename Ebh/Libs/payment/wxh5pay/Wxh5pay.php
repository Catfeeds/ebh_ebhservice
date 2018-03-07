<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:55
 */
require_once __DIR__.'/../WxpayAPI_php_v3/lib/WxPay.Api.php';
require_once __DIR__.'/../WxpayAPI_php_v3/lib/WxPay.Notify.php';
class Wxh5pay implements Payment{
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
        $input = new WxPayUnifiedOrder();

        $input->SetBody(preg_replace("/\s/","", $parameters['body']));
        $input->SetAttach($parameters['attach']);
        $input->SetOut_trade_no($parameters['out_trade_no']);
        $input->SetTotal_fee($parameters['total_fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag(shortstr($parameters['subject'],80,''));
        $notify = isset($parameters['notify_url']) ? $parameters['notify_url'] : 'notify_url';
        $input->SetNotify_url($this->config[$notify]);
        $input->SetTrade_type("MWEB");
        $input->SetProduct_id($parameters['out_trade_no']);
        log_message('H5支付IP：'.$_POST['realip']);
        $scene_info = array('h5_info'=>array('type'=>'Wap','wap_url'=>'http://wap.ebh.net/','wap_name'=>'网络学校'));
        $input->SetSpbill_create_ip($_POST['realip']);
        $input->SetSceneInfo(json_encode($scene_info),JSON_UNESCAPED_UNICODE);
        $order = WxPayApi::unifiedOrder($input);
        if($order['return_code'] == 'FAIL'){
            log_message('生成微信支付订单失败:'.json_encode($order));
            throw new Exception_InternalServerError('生成支付订单失败');
        }

        return $order;
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


        $buyer_id = $data['openid'];
        $buyer_info = '';
        //处理订单回调
        $order = Transaction::notifyOrder($data['out_trade_no'],$data['transaction_id'],array('buyer_id'=>$buyer_id,'buyer_info'=>$buyer_info));

        if(empty($order)) {//订单不存在
            return false;
        }
        if($order['status'] == 1) {//订单已处理，则不重复处理
            return true;
        }
        return true;
    }
}