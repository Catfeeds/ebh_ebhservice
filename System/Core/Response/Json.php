<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Response_Json extends Response{
    public function __construct() {
        $this->addHeaders('Content-Type', 'application/json;charset=utf-8');
    }

    protected function formatResult($result) {
        return json_encode($result);
    }
}