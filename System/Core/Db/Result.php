<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
interface Db_Result {
    public function __construct($obj);

    public function row_array();
    /**
     * 返回查询列表
     * @param string $key 列表键字段名
     * @param string $prefix 键前辍，主要用于将键转成字符串用于array_merge操作
     * @return mixed
     */
    public function list_array($key = '', $prefix = '');
    /**
     * 返回查询一维数组
     * @param string $field 值字段名，为空时以查询的第一个字段为值
     * @param string $key 键字段名,为空时以顺序数字为键
     * @return mixed
     */
    public function list_field($field = '', $key = '');
    public function __destruct();
}