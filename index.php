<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */


//加载初始化文件
defined('APP_PATH') || define('APP_PATH', dirname(__FILE__). DIRECTORY_SEPARATOR . 'Ebh');
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .'init.php';

//设置应用路径
Ebh()->loader->setApplicationDir('Ebh');

$api = new EbhService();

$result = $api->response();

$result->output();

