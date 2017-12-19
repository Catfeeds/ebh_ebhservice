<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:41
 * 支付配置信息
 */

return array(
    'paytype'   =>  array('wxapppay','balance'),//支持的支付配置
    //微信小程序支付配置
    'wxapppay'  =>  array(
        'class' =>  'wxpay',
        'config'    =>  array(
            'appid' =>  'wxd31461195ec7ef3a',
            'mchid' =>  '1230701901',
            'key'   =>  '8934e7d11231e97507ef794df7b0519d',
            'appsecret' =>  '2ad67d0ffa182190dad2253a97e452d7',
            'sslcert_path'  =>  '',
            'sslkey_path'   =>  '',
            'notify_url'    =>  'http://wxpayhaha.ngrok.cc/Notify/wxapppay'
        )
    ),
    //余额支付配置
    'balance'   =>  array(
        'class' =>  'balance',
        'config'    =>  array()
    )
);
