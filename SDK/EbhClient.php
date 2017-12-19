<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
class EbhClient{
    protected $appid;
    protected $appsecret;
    protected $host;//接口域名
    protected $service;//服务名称
    protected $params = array();//传入参数
    protected $timeout = 3000;//超时时间
    protected $filter = NULL;//过滤器
    protected $parser = NULL;

    public function __construct($appid = '',$appsecret = ''){
        $this->appid = $appid;
        $this->appsecret = $appsecret;

        $this->params['appid'] = $appid;
    }

    public function init($appid,$appsecret){
        $this->appid = $appid;
        $this->params['appid'] = $appid;
        $this->appsecret = $appsecret;
    }

    public function setHost($host){
        $this->host = $host;
        return $this;
    }
    //设置服务名称
    public function setService($service){
        $this->service = $service;
        return $this;
    }
    //添加参数
    public function addParams($name , $value = ''){
        if(is_array($name)){
            foreach($name as $k=>$v){
                $this->params[$k] = $v;
            }
        }else{
            $this->params[$name] = $value;
        }
        return $this;
    }
    //设置超时时间
    public function setTimeout($time){
        $this->timeout = $time;
        return $this;
    }
    //设置过滤器 用于生成签名
    public function setFilter(EbhClientFilter $filter){
        $this->filter = $filter;
        return $this;
    }
    //设置结果解析
    public function setParser(EbhClientParser $parser){
        $this->parser = $parser;
        return $this;
    }
    //重设配置
    public function reSetting(){
        $this->service = "";
        $this->params = array();
        $this->params['appid'] = $this->appid;
        return $this;
    }

    public function request(){
        $url = $this->host;
        if(!empty($this->service)){
            //$url .= '?service=' . $this->service;
            $url .= str_replace(".","/",$this->service);
        }
        if ($this->filter !== NULL) {
            $this->filter->filter($this->service, $this->params);
        }

        $rs = $this->doRequest($url, $this->params, $this->timeout);

        if($this->parser != NULL){
            return $this->parser->parse($rs);
        }else{
            return $rs;
        }
    }


    protected function doRequest($url,$data,$timeout = 3000){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $rs = curl_exec($ch);

        curl_close($ch);

        return $rs;
    }

}


/**
 * 接口过滤器
 *
 * - 可用于接口签名生成
 */
interface EbhClientFilter {

    /**
     * 过滤操作
     * @param string $service 接口服务名称
     * @param array $params 接口参数，注意是引用。可以直接修改
     * @return null
     */
    public function filter($service, array &$params);
}

/**
 * 接口结果解析器
 *
 * - 可用于不同接口返回格式的处理
 */
interface EbhClientParser {

    /**
     * 结果解析
     * @param string $apiResult
     * @return PhalApiClientResponse
     */
    public function parse($result);
}


class FilterDemo implements  EbhClientFilter{
    public $appsecret;
    public function __construct($appsecret){

        $this->appsecret = $appsecret;

    }

    public function filter($service, array &$params){
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($params);

        //对待签名的参数数组排序

        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串

        $prestr = $this->createLinkstring($para_filter);

        $sign = $this->md5Sign($prestr);
        $params['sign'] = $sign;
    }
    protected function md5Sign($data){
        return md5($data.$this->appsecret);
    }

    /**
     * 过滤参数中值为空 或则参数名为sign的参数
     * @param $params
     * @return array
     */
    protected function paraFilter($params){
        $para_filter = array();
        while(list($key,$val) = each($params)){
            if($key == 'sign' || $val === ''){
                continue;
            }else{
                $para_filter[$key] = $params[$key];
            }
        }
        return $para_filter;
    }
    //参数数组排序
    protected function argSort($params){
        ksort($params);
        reset($params);
        return $params;
    }


    function createLinkstring($para) {
        return urldecode(http_build_query($para));


    }
}

class ParserDemo implements EbhClientParser{
    public function parse($result){
        echo $result;exit;
        return json_decode($result,true);
    }
}
