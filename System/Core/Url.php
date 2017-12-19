<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Url {
    private $route = array();   //路由配置数组
    private $query_string = '';
    private $path;
    private $segments = array();//地址分段信息
    private $directory = '';//路径
    private $control = '';//控制器
    private $method = '';//方法
    public function __construct() {

        $this->route = Ebh()->config->get('system.route');

    }

    /**
     * 检测URl
     * @return string
     */
    public function detectUrl(){
        $path = $_SERVER['REQUEST_URI'];
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        if (isset($this->route['suffix']) && ($pathi = stripos($path,$this->route['suffix'].'?')) !== FALSE) {
            $spath = $path;
            $path = substr($spath, 0,$pathi);
            $this->query_string = substr($spath, $pathi + strlen($this->route['suffix']) + 1);
        }
        if (isset($this->route['suffix']) && substr($path, strlen($path) - strlen($this->route['suffix'])) == $this->route['suffix']) {
            $path = substr($path, 0, strlen($path) - strlen($this->route['suffix']));
        }
        if (substr($path, 0, 1) == '?') {
            $this->query_string = substr($path,1);
            $path = '';
        }

        $this->path = $path;
        return $path;
    }

    /**
     * 解析URl参数
     */
    public function parseUrl(){
        if (!isset($this->path)) {
            $this->detectUrl();
        }
        return $this->_parseUrl($this->path);
    }

    public function _parseUrl($path){
        if (!empty($path)) {
            $this->segments = explode('/', $path);
        }
        $segcount = count($this->segments);
        if($segcount == 1){

        }else{
            for ($i = 0; $i < $segcount ; $i ++) {
                if ($i == 0 && file_exists(APP_PATH . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . ucfirst($this->segments[0]))) {
                    $this->directory = ucfirst($this->segments[0]);
                }else{
                    if (empty($this->control)){
                        $this->control = $this->segments[$i];
                    }else{
                        if(empty($this->method)){
                            $this->method = $this->segments[$i];
                        }
                    }
                }
            }
        }
        return $this->segments;
    }

    public function getPath(){
        return $this->path;
    }

    public function getDirectory(){
        return $this->directory;
    }

    public function getControl(){
        return $this->control;
    }

    public function getMethod(){
        return $this->method;
    }


}