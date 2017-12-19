<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:44
 */

interface Payment{

    /**
     * 获取支付信息
     * @param $order
     * @param $parameters 附加参数
     * @return mixed
     */
    function getPaymentCode($order,$parameters);

    /**
     * 响应通知
     * @param $request
     * @return mixed
     */
    function notify($request = array());
}