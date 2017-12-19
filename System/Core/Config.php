<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 配置接口类
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
interface Config {
    /**
     * 获取配置
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = NULL);
}