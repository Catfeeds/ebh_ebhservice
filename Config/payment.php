<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 14:41
 * 支付配置信息
 *
 *
 * class:类名
 * name:支付名称
 * runtype:执行方式(qrcode = 二维码 ,window = 新窗口直接打开 , ajax = 异步 , native = 调用客户端原生方法 , 'url' = 网址) 预留
 * config : 支付配置
 * scope:作用域 (预留)  1=PC 2=wap 3=app 4=小程序 5=微信浏览器
 */

return array(
    'paytype'   =>  array('wxapppay','balance','wxpayqrcode','wxpublicpay','wxh5pay','alipay','alipayqrcode','abcpay'),//支持的支付配置
    'abcpay'  =>  array(
        'class' =>  'abcpay',
        'name'  =>  '农行直通车',
        'runtype'   =>  'url',
        'config'    =>  array(
            'notify_url'    =>  'http://svn1.ebh.net:83/notify/abcpay.html',
        ),
        'scope' =>  array(1)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //支付宝扫码
    'alipayqrcode'  =>  array(
        'class' =>  'alipayqrcode',
        'name'  =>  '支付宝扫码支付',
        'runtype'   =>  'window',
        'config'    =>  array(
            //合作身份者id，以2088开头的16位纯数字
            'partner' =>  '2088701923923127',
            //安全检验码，以数字和字母组成的32位字符
            'key'   =>  '2s3dcobrsyqu505lml6klrn6m5bvml8b',
            //签约支付宝账号或卖家支付宝帐户
            'seller_email'  =>  'ebanhui@qq.com',
            //签名方式 不需修改
            'sign_type' =>  strtoupper('MD5'),
            //字符编码格式 目前支持 gbk 或 utf-8
            'input_charset' =>  strtolower('utf-8'),
            //ca证书路径地址，用于curl中ssl校验
            //请保证cacert.pem文件在当前文件夹目录中
            'cacert'    =>  EBH_ROOT . DIRECTORY_SEPARATOR . 'Ebh' . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR  . 'payment' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'alipay' . DIRECTORY_SEPARATOR . 'cacert.pem',
            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            'transport' =>  'http',
            'notify_url'    =>  'http://svn1.ebh.net:83/notify/alipayqrcode.html',
            // 'notify_url'    =>  'http://wxpayhaha.ngrok.cc/Notify/wxpayqrcode',
        ),
        'scope' =>  array(1)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //支付宝支付
    'alipay'  =>  array(
        'class' =>  'alipay',
        'name'  =>  '支付宝支付',
        'runtype'   =>  'window',
        'config'    =>  array(
            //合作身份者id，以2088开头的16位纯数字
            'partner' =>  '2088701923923127',
            //安全检验码，以数字和字母组成的32位字符
            'key'   =>  '2s3dcobrsyqu505lml6klrn6m5bvml8b',
            //签约支付宝账号或卖家支付宝帐户
            'seller_email'  =>  'ebanhui@qq.com',
            //签名方式 不需修改
            'sign_type' =>  strtoupper('MD5'),
            //字符编码格式 目前支持 gbk 或 utf-8
            'input_charset' =>  strtolower('utf-8'),
            //ca证书路径地址，用于curl中ssl校验
            //请保证cacert.pem文件在当前文件夹目录中
            'cacert'    =>  EBH_ROOT . DIRECTORY_SEPARATOR . 'Ebh' . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR  . 'payment' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'alipay' . DIRECTORY_SEPARATOR . 'cacert.pem',
            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            'transport' =>  'http',
            'notify_url'    =>  'http://svn1.ebh.net:83/notify/alipay.html',
            // 'notify_url'    =>  'http://wxpayhaha.ngrok.cc/Notify/wxpayqrcode',
        ),
        'scope' =>  array(1)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //微信公众号支付
    'wxh5pay'  =>  array(
        'class' =>  'wxh5pay',
        'name'  =>  '微信支付',
        'runtype'   =>  'window',
        'config'    =>  array(
            'appid' =>  'wx0ee6bf56757c7dfe',
            'mchid' =>  '1230701901',
            'key'   =>  '8934e7d11231e97507ef794df7b0519d',
            'appsecret' =>  'b6b086d176f2bb6c0c10f94dfb56a03a',
            'sslcert_path'  =>  '',
            'sslkey_path'   =>  '',
            'notify_url'    =>  'http://svn1.ebh.net:83/notify/wxh5pay.html',
        ),
        'scope' =>  array(2)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //微信公众号支付
    'wxpublicpay'  =>  array(
        'class' =>  'wxpublicpay',
        'name'  =>  '微信支付',
        'runtype'   =>  'native',
        'config'    =>  array(
            'appid' =>  'wx975d8f85a286b019',
            'mchid' =>  '10060524',
            'key'   =>  '70db761c04168bb38f5944433c992167',
            'appsecret' =>  '48888888888888888888888888888887',
            'sslcert_path'  =>  '',
            'sslkey_path'   =>  '',
            'notify_url'    =>  'http://svn1.ebh.net:83/notify/wxpublicpay.html',
        ),
        'scope' =>  array(5)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //微信二维码支付
    'wxpayqrcode'  =>  array(
        'class' =>  'wxpayqrcode',
        'name'  =>  '微信支付',
        'runtype'   =>  'qrcode',
        'config'    =>  array(
            'appid' =>  'wx975d8f85a286b019',
            'mchid' =>  '10060524',
            'key'   =>  '70db761c04168bb38f5944433c992167',
            'appsecret' =>  '48888888888888888888888888888887',
            'sslcert_path'  =>  '',
            'sslkey_path'   =>  '',
            'notify_url'    =>  'http://svn1.ebh.net:83/notify/wxpayqrcode.html',
           // 'notify_url'    =>  'http://wxpayhaha.ngrok.cc/Notify/wxpayqrcode',
        ),
        'scope' =>  array(1,2)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //微信小程序支付配置
    'wxapppay'  =>  array(
        'class' =>  'wxpay',
        'name'  =>  '微信支付',
        'runtype'   =>  'native',
        'config'    =>  array(
            'appid' =>  'wxd31461195ec7ef3a',
            'mchid' =>  '1230701901',
            'key'   =>  '8934e7d11231e97507ef794df7b0519d',
            'appsecret' =>  '2ad67d0ffa182190dad2253a97e452d7',
            'sslcert_path'  =>  '',
            'sslkey_path'   =>  '',
            'notify_url'    =>  'http://wxpayhaha.ngrok.cc/notify/wxapppay.html',
        ),
        'scope' =>  array(4)//作用域 1=PC 2=wap 3=app 4=小程序
    ),
    //余额支付配置
    'balance'   =>  array(
        'class' =>  'balance',
        'name'  =>  '余额支付',
        'runtype'   =>  'ajax',
        'config'    =>  array(),
        'scope' =>  array(1,2,3,4)
    )
);
