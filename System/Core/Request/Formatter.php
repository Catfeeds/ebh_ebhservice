<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 参数格式化接口
 */
interface Request_Formatter{
    public function parse($value, $rule);
}