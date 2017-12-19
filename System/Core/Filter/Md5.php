<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Filter_Md5 implements Filter{

    protected $signName;

    public function __construct($signName = 'sign') {
        $this->signName = $signName;
    }



    public function check(){
        $allParams = Ebh()->request->getAll();
        if (empty($allParams)) {
            return;
        }

        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($allParams);

        //对待签名的参数数组排序

        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串

        $prestr = $this->createLinkstring($para_filter);


        $appsecret = Ebh()->config->get('application.'.$allParams['appid']);

        if($appsecret == null){
            throw new Exception_BadRequest('wrong appid');
        }
        $rs = $this->md5Verify($prestr,$appsecret,$allParams[$this->signName]);

        if(!$rs){
            Ebh()->log->debug('Wrong Sign', $allParams);
            throw new Exception_BadRequest('wrong sign');
        }

    }



    public function md5Verify($data,$appsecret,$sign){
        return md5($data.$appsecret) ==  $sign;
    }


    protected function paraFilter($params){
        $para_filter = array();
        while(list($key,$val) = each($params)){
            if($key == 'sign' || $val === '' || $key == 'service'){
                continue;
            }else{
                $para_filter[$key] = $params[$key];
            }
        }
        return $para_filter;
    }

    protected function argSort($params){
        ksort($params);
        reset($params);
        return $params;
    }


    function createLinkstring($para) {
       /* $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key.'='.$val.'&';
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;*/

        return urldecode(http_build_query($para));


    }
}