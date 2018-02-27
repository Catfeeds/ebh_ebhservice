<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:48
 */
require_once __DIR__.'/../common/alipay/lib/alipay_submit.class.php';
require_once __DIR__.'/../common/alipay/lib/alipay_notify.class.php';
class Alipay implements Payment{
    private $config = array();
    public function __construct($config){
        $this->config = $config;
    }

    public function getPaymentCode($order,$parameters = array()){
        if(!isset($parameters['curdomain'])){
            throw new Exception_BadRequest('缺少必要附加参数 curdomain');
        }
        $domain = $order['itemlist'][0]['domain'];

        $isthird = false;
        if(isset($parameters['curdomain']) && $parameters['curdomain'] != 'ebh.net' && $parameters['curdomain'] != 'ebanhui.com') {
            $isthird = true;
        }
        if($isthird) {
            //页面跳转同步通知页面路径
            $return_url = 'http://'.$parameters['curdomain'].'/ibuy/alireturn.html?orderid='.$order['orderid'];
            //商品展示地址
            $show_url = 'http://'.$parameters['curdomain'];

        } else {
            //页面跳转同步通知页面路径
            $return_url = 'http://'.$domain.'.'.$parameters['curdomain'].'/ibuy/alireturn.html?orderid='.$order['orderid'];
            //商品展示地址
            $show_url = 'http://'.$domain.'.'.$parameters['curdomain'];
        }

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify = isset($parameters['notify_url']) ? $parameters['notify_url'] : 'notify_url';
        $notify_url = $this->config[$notify];
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = $return_url;
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //必填
        //商户订单号
        $out_trade_no = $parameters['out_trade_no'];
        //商户网站订单系统中唯一订单号，必填
        //订单名称
        $subject = $parameters['subject'];
        //必填
        //付款金额
        $total_fee = $parameters['total_fee'];
        //必填
        //订单描述
        $body = $parameters['body'];
        //商品展示地址
        $show_url = $show_url;
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1
        //构造要请求的参数数组，无需改动
        $alipayParams = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($this->config['partner']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "seller_email"	=> $this->config['seller_email'],
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "show_url"	=> $show_url,
            "anti_phishing_key"	=> $anti_phishing_key,
            "exter_invoke_ip"	=> $exter_invoke_ip,
            "_input_charset"	=> trim(strtolower($this->config['input_charset']))
        );
        if(!empty($parameters['fromwap'])){
            $alipayParams['service'] = 'alipay.wap.create.direct.pay.by.user';
            $alipayParams['seller_id'] = $alipayParams['partner'];
            $alipayParams['app_pay'] = 'Y';
        }

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->config);
        $html_text = $alipaySubmit->buildRequestForm($alipayParams,"get", "确认");
        $html_text = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
            '<html>'.
            '<head>'.
            '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
            '<title>支付宝即时到账交易接口</title>'.
            '</head>'.
            $html_text.
            '</body>'.
            '</html>';

        return $html_text;
    }

    /**
     * 支付宝异步回调
     * @param array $request
     */
    public function notify($request = array()){
        $alipayNotify = new AlipayNotify($this->config);
        $verify_result = $alipayNotify->verifyNotify();
        if(!$verify_result) {	//验证不通过
            echo "fail";
            exit;
        }
        //处理订单回调
        $buyer_id = $_POST['buyer_id'];
        $buyer_info = $_POST['buyer_email'];
        $param = array('buyer_id'=>$buyer_id,'buyer_info'=>$buyer_info);
        Transaction::notifyOrder($_POST['out_trade_no'],$_POST['trade_no'],$param);
        //验证通过
        echo "success";
        exit;
    }
}