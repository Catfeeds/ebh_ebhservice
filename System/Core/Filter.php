<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 拦截器接口
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
interface Filter {
    public function check();
}