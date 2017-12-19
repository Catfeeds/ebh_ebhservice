<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 客户端非法请求
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Exception_BadRequest extends EbhException{
    public function __construct($message, $code = 0) {
        parent::__construct(
            $message,400+$code
        );
    }
}