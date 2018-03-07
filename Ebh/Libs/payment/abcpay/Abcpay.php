<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:27
 */
require_once __DIR__.'/../common/abcpay/ebusclient/PaymentRequest.php';
class Abcpay implements Payment{
    private $config = array();
    public function __construct($config){
        $this->config = $config;
    }
    public function getPaymentCode($order,$parameters = array()){
        if(!isset($parameters['curdomain'])){
            throw new Exception_BadRequest('缺少必要附加参数 curdomain');
        }
        $tRequest=new PaymentRequest();
        //var_dump($parameters);exit;

        //var_dump($order);exit;
        //转换编号 UTF-8到 GB2312
        $OrderDesc = shortstr($parameters['subject'],47);
        $domain = $order['itemlist'][0]['domain'];
        $tRequest->order["PayTypeID"] = 'ImmediatePay'; //设定交易类型(直接支付)
        $tRequest->order["OrderNo"] = $order['orderid']; //设定订单编号
        $tRequest->order["ExpiredDate"] = 10; //设定订单保存时间
        $tRequest->order["OrderAmount"] = round($order['totalfee'],2); //设定交易金额
        $tRequest->order["Fee"] = ''; //设定手续费金额
        $tRequest->order["CurrencyCode"] = '156'; //设定交易币种 156 人民币
        $tRequest->order["ReceiverAddress"] = ''; //收货地址
        $tRequest->order["InstallmentMark"] = '0'; //分期标识
        $tRequest->order["BuyIP"] = $order['ip']; //IP
        $tRequest->order["OrderDesc"] = $OrderDesc; //设定订单说明

        $tRequest->order["OrderURL"] = 'http://'.$domain.'.ebh.net/';			//订单查询地址; //设定订单地址
        $tRequest->order["OrderDate"] = date('Y/m/d',SYSTIME); //设定订单日期 （必要信息 - YYYY/MM/DD）
        $tRequest->order["OrderTime"] = date('H:i:s',SYSTIME); //设定订单时间 （必要信息 - HH:MM:SS）
        $tRequest->order["CommodityType"] = '0201'; //设置商品种类


        $i = 0;
        foreach($order['itemlist'] as $myitem) {
            if($i > 1)
                break;
            $myitem['oname'] = shortstr($myitem['oname'],47,'');
            $orderitem = array ();
            $orderitem["SubMerName"] = "e板会"; //设定二级商户名称
            $orderitem["SubMerId"] = "0"; //设定二级商户代码
            $orderitem["SubMerMCC"] = "0000"; //设定二级商户MCC码
            $orderitem["SubMerchantRemarks"] = "浙江新盛蓝科技有限公司"; //二级商户备注项
            $orderitem["ProductID"] = $myitem['itemid']; //商品代码，预留字段
            $orderitem["ProductName"] = $myitem['oname']; //商品名称
            $orderitem["UnitPrice"] = $myitem['fee']; //商品总价
            $orderitem["Qty"] = "1"; //商品数量
            $orderitem["ProductRemarks"] = $myitem['oname']; //商品备注项
            $orderitem["ProductType"] = "消费类"; //商品类型
            //$orderitem["ProductDiscount"] = "0.9"; //商品折扣
            $orderitem["ProductExpiredDate"] = "10"; //商品有效期
            $tRequest->orderitems[$i] = $orderitem;
            $i ++;
        }

        //3、生成支付请求对象
        if(!isset($parameters['PaymentType']) && in_array($parameters['PaymentType'],array('A',6))){
            $tRequest->request["PaymentType"] = $parameters['PaymentType']; //设定支付类型 A是合并支付，6为银联支付
        }else{
            $tRequest->request["PaymentType"] = 1;
        }
        //$tRequest->request["PaymentType"] = '6'; //设定支付类型 A是合并支付，6为银联支付

        $tPaymentLinkType = 1;	//接入方式       （必要信息）1：internet 网络接入 2：手机网络接入 3:数字电视网络接入 4:智能客户端

        if(isset($parameters['ismobile']) && $parameters['ismobile'] == 1 ){
            $tPaymentLinkType = 2;
        }
        $tRequest->request["PaymentLinkType"] = $tPaymentLinkType; //设定支付接入方式

        if($tRequest->request["PaymentType"] == 6 && $tRequest->request["PaymentLinkType"] == 2){
            $tRequest->request["PaymentLinkType"] = 1;
        }
        $tRequest->request["ReceiveAccount"] = ''; //设定收款方账号
        $tRequest->request["ReceiveAccName"] = ''; //设定收款方户名
        $tNotifyType = 1;	//设定支付结果通知方式（必要信息，0：URL 页面通知  1：服务器通知）
        $tRequest->request["NotifyType"] = $tNotifyType.''; //设定通知方式



        $isthird = false;
        if(isset($parameters['curdomain']) && $parameters['curdomain'] != 'ebh.net' && $parameters['curdomain'] != 'ebanhui.com') {
            $isthird = true;
        }

        if($isthird) {
            $notify_url = 'http://'.$parameters['curdomain'].'/notify/abcpay.html?orderid='.$order['orderid'];
        } else {
            $notify_url = 'http://'.$domain.'.'.$parameters['curdomain'].'/notify/abcpay.html?orderid='.$order['orderid'];
        }

        /*$notify = isset($parameters['notify_url']) ? $parameters['notify_url'] : 'notify_url';
        $notify_url = $this->config[$notify];*/
        $tRequest->request["ResultNotifyURL"] = $notify_url; //设定通知URL地址
        if(strlen($order['remark']) > 100) {
            $MerchantRemarks = shortstr($order['remark'],47);
        }else{
            $MerchantRemarks = $order['remark'];
        }

        $tRequest->request["MerchantRemarks"] = $MerchantRemarks; //设定附言
        $tRequest->request["IsBreakAccount"] = '0'; //设定交易是否分账
        $tRequest->request["SplitAccTemplate"] = ''; //分账模版编号
        $tResponse = $tRequest->postRequest();
        if($tResponse->isSuccess())
        { //6、支付请求提交成功，将客户端导向支付页面
            $paymentUrl=$tResponse->getValue('PaymentURL');
            return $paymentUrl;
        }
        throw new Exception_InternalServerError('生成交易信息失败');

    }

    public function notify($request = array()){
        class_exists('Result') or require_once  __DIR__.'/../common/abcpay/ebusclient/Result.php';
        $tResult = new Result();
        //$_POST['MSG'] = 'PE1TRz48TWVzc2FnZT48VHJ4UmVzcG9uc2U+PFJldHVybkNvZGU+MDAwMDwvUmV0dXJuQ29kZT48RXJyb3JNZXNzYWdlPr270tezybmmPC9FcnJvck1lc3NhZ2U+PEVDTWVyY2hhbnRUeXBlPkVCVVM8L0VDTWVyY2hhbnRUeXBlPjxNZXJjaGFudElEPjEwMzg4MTkwOTk5MDAzNTwvTWVyY2hhbnRJRD48VHJ4VHlwZT5QYXlSZXE8L1RyeFR5cGU+PE9yZGVyTm8+NzY0MzwvT3JkZXJObz48QW1vdW50PjAuMTA8L0Ftb3VudD48QmF0Y2hObz4wMDE3MTQ8L0JhdGNoTm8+PFZvdWNoZXJObz4wMTYyODM8L1ZvdWNoZXJObz48SG9zdERhdGU+MjAxOC8xLzk8L0hvc3REYXRlPjxIb3N0VGltZT4xMDoyODo1ODwvSG9zdFRpbWU+PFBheVR5cGU+RVAwNTU8L1BheVR5cGU+PE5vdGlmeVR5cGU+MTwvTm90aWZ5VHlwZT48UGF5SVA+MTkyLjE2OC4wLjEyMzwvUGF5SVA+PGlSc3BSZWY+MTlFQ0VQMDExMDA3MDY4MzY5MDk8L2lSc3BSZWY+PC9UcnhSZXNwb25zZT48L01lc3NhZ2U+PFNpZ25hdHVyZS1BbGdvcml0aG0+U0hBMXdpdGhSU0E8L1NpZ25hdHVyZS1BbGdvcml0aG0+PFNpZ25hdHVyZT4wSGhUNERzMkorTjZ3d1BIcFExK2l3a1ZGcytmVExkQkFGTWc4ZXB4Z2RqSlRRbTdybGtSdTBEQlZuanZaNHhQZjFMOTBiNU5ic2xCcktDem5idHdjTGNKVTcyM3ZIWUEzbSt3Wk9OeEtKUVBUNGtmOXA5alpUNTJhUC9CMjEzOGlxWnJRL3QvUXZBMkJaUjhibHV2U1ZJN3dDT3hFTjljWkQwWGZ4d25wLzg9PC9TaWduYXR1cmU+PC9NU0c+';
        $tResponse = $tResult->init($_POST['MSG']);
        $mynotify = false;

        if($tResponse->isSuccess()) {
            $mynotify = array();
            $mynotify['TrxType'] = $tResponse->getValue('TrxType');
            $mynotify['OrderNo'] = $tResponse->getValue('OrderNo');
            $mynotify['Amount']  = $tResponse->getValue('Amount');
            $mynotify['BatchNo'] = $tResponse->getValue('BatchNo');
            $mynotify['VoucherNo'] = $tResponse->getValue('VoucherNo');
            $mynotify['HostDate']  = $tResponse->getValue('HostDate');
            $mynotify['HostTime'] = $tResponse->getValue('HostTime');
            $mynotify['MerchantRemarks'] = $tResponse->getValue('MerchantRemarks');
            $mynotify['PayType'] = $tResponse->getValue('PayType');
            $mynotify['NotifyType'] = $tResponse->getValue('NotifyType');
            $mynotify['TrnxNo'] = $tResponse->getValue('iRspRef');
            //处理交易
            Transaction::notifyOrder($mynotify['OrderNo'],$mynotify['VoucherNo']);
            echo "success";
        }else {
            $ReturnCode   = $tResponse->getReturnCode();
            $ErrorMessage = $tResponse->getErrorMessage();
            log_message("abc pay error,ReturnCode:$ReturnCode,ErrorMessage:$ErrorMessage");
            echo "fail";
        }

    }
}