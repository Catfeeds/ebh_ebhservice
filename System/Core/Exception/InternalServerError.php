<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 服务器运行异常错误
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Exception_InternalServerError extends EbhException{
    public function __construct($message, $code = 0) {
        parent::__construct(
            $message,500+$code
        );
    }
}