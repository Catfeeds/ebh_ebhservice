<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
interface Db_Driver{
    function __construct($dbhost,$dbuser,$dbpw,$dbname,$dbport = '');
    //设置数据库字符集
    public function _set_charset($charset);
    //执行数据库脚本
    public function _execute($sql);
    //读取数据库错误
    public function error_msg();
    //返回新生成的ID
    public function _insert_id();
}