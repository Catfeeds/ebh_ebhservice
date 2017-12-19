<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 响应类
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
abstract class Response{

    /**
     * @var int
     * 响应状态码
     *
     * 200:成功
     * 400:非法请求
     * 500:服务器错误
     */
    protected $ret = 200;

    /**
     * @var array 等待返回客户端的数据
     */
    protected $data = array();
    /**
     * 等待返回的错误信息
     * @var string
     */
    protected $msg = '';
    /**
     * @var array
     * 响应头
     */
    protected $headers = array();

    /**
     * @param $ret
     * @return $this
     * 设置返回状态码 200:成功 400:非法请求 500:服务器错误
     */
    public function setRet($ret) {
        $this->ret = $ret;
        return $this;
    }

    /**
     * 设置返回数据
     * @param $data
     * @return $this
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置错误信息
     * @param $msg
     * @return $this
     */
    public function setMsg($msg) {
        $this->msg = $msg;
        return $this;
    }

    /**
     * 添加响应头
     * @param $key
     * @param $content
     */
    public function addHeaders($key, $content) {
        $this->headers[$key] = $content;
    }

    /**
     * 结果输出
     */
    public function output() {
        $this->handleHeaders($this->headers);

        $rs = $this->getResult();
        ob_clean();
        echo $this->formatResult($rs);
    }

    public function getResult() {
        $rs = array(
            'ret' => $this->ret,
            'data' => $this->data,
            'msg' => $this->msg,
        );

        return $rs;
    }

    /**
     * 获取header信息
     * @param null $key
     * @return array|null
     */
    public function getHeaders($key = NULL) {
        if ($key === NULL) {
            return $this->headers;
        }

        return isset($this->headers[$key]) ? $this->headers[$key] : NULL;
    }


    protected function handleHeaders($headers) {
        foreach ($headers as $key => $content) {
            @header($key . ': ' . $content);
        }
    }

    /**
     * 格式化需要输出的内容
     * @param $result
     * @return mixed
     */
    abstract protected function formatResult($result);
}