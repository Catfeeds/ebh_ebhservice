<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 *
 *
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Ebh implements ArrayAccess{
    /**
     * @var Ebh::$instance 单例
     */
    protected static $instance = NULL;



    protected $hitTimes = array();
    /**
     * @var array 注册的服务池
     */
    protected $data = array();

    public function __construct() {

    }

    /**
     * 获取Ebh单体实例
     * @return Ebh
     */
    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new Ebh();
            self::$instance->onConstruct();
        }
        return self::$instance;
    }


    public function onConstruct() {
        $this->request = 'Request';
        $this->response = 'Response_Json';
    }


    /**
     * 统一setter
     *
     * - 1、设置保存service的构造原型，延时创建
     *
     * @param string $key service注册名称，要求唯一，区分大小写
     * @parms mixed $value service的值，可以是具体的值或实例、类名、匿名函数、数组配置
     */
    public function set($key, $value) {
        $this->resetHit($key);

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * 统一getter
     *
     * - 1、获取指定service的值，并根据其原型分不同情况创建
     * - 2、首次创建时，如果service级的构造函数可调用，则调用
     *
     * @param string $key service注册名称，要求唯一，区分大小写
     * @param mixed $default service不存在时的默认值
     * @return mixed 没有此服务时返回NULL
     */
    public function get($key, $default = NULL) {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $default;
        }

        $this->recordHitTimes($key);

        if ($this->isFirstHit($key)) {
            $this->data[$key] = $this->initService($this->data[$key]);
        }

        return $this->data[$key];
    }

    /** ------------------ 魔法方法 ------------------ **/
    public function __call($name, $arguments) {

    }


    public function __set($name, $value) {
        $this->set($name, $value);

    }

    public function __get($name) {
        return $this->get($name, NULL);
    }




    /** ------------------ ArrayAccess（数组式访问）接口 ------------------ **/

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetGet($offset) {
        return $this->get($offset, NULL);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }



    protected function initService($config) {
        $rs = NULL;
        if ($config instanceOf Closure) {
            $rs = $config();
        }elseif (is_string($config) && class_exists($config)) {
            $rs = new $config();
            if(is_callable(array($rs, 'onInitialize'))) {
                call_user_func(array($rs, 'onInitialize'));
            }
        } else {
            $rs = $config;
        }
        return $rs;
    }

    protected function resetHit($key) {
        $this->hitTimes[$key] = 0;
    }

    protected function isFirstHit($key) {
        return $this->hitTimes[$key] == 1;
    }

    protected function recordHitTimes($key) {
        if (!isset($this->hitTimes[$key])) {
            $this->hitTimes[$key] = 0;
        }

        $this->hitTimes[$key] ++;
    }
}