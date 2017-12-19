<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Request {


    protected $data = array();

    protected $headers = array();

    /**
     * @param null $data
     *
     * 参数来源，可以为：$_GET/$_POST/$_REQUEST/自定义
     */
    public function __construct($data = NULL) {
        $this->data    = $this->genData($data);
        $this->headers = $this->getAllHeaders();
    }

    /**
     * 生成请求参数
     *
     * @param $data
     * @return mixed
     */
    protected function genData($data) {
        if (!isset($data) || !is_array($data)) {
            return $_REQUEST;
        }

        return $data;
    }


    /**
     * 初始化请求头信息
     * @return array
     */
    protected function getAllHeaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (is_array($value) || substr($name, 0, 5) != 'HTTP_') {
                continue;
            }

            $headerKey = implode('-', array_map('ucwords', explode('_', strtolower(substr($name, 5)))));
            $headers[$headerKey] = $value;
        }

        return $headers;
    }


    /**
     * 获取请求Header参数
     *
     * @param string $key     Header-key值
     * @param mixed  $default 默认值
     *
     * @return string
     */
    public function getHeader($key, $default = NULL) {
        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    /**
     * 直接获取接口参数
     *
     * @param string $key     接口参数名字
     * @param mixed  $default 默认值
     *
     * @return 
     */
    public function get($key, $default = NULL) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * 根据提供的规则获取参数
     * @param $rule array('name' => '', 'type' => '', 'defalt' => ...) 参数规则
     */
    public function getByRule($rule) {
        $rs = NULL;

        if (!isset($rule['name'])) {
            throw new Exception_InternalServerError('miss name for rule');
        }

        $rs = Request_Parameter::format($rule['name'], $rule, $this->data);

        if ($rs === NULL && (isset($rule['require']) && $rule['require'])) {
            throw new Exception_BadRequest("{$rule['name']} require, but miss");
        }

        return $rs;

    }





    /**
     * 获取全部接口参数
     * @return array
     */
    public function getAll() {
        return $this->data;
    }
}