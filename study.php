<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */


//加载初始化文件
//初始化，添加对CLI形式的支持
if(php_sapi_name() === "cli") {	//如果是cli方式 则后面的第一个参数为URI 如 php /data0/htdocs/ebhservice/study.php /study/logtask/doTask
	$uri = '/';
	if (count($argv) > 1) {
		$uri = $argv[1];
	}
	$_SERVER['REQUEST_URI'] = $uri;
}
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .'init.php';
defined('APP_PATH') || define('APP_PATH', dirname(__FILE__). DIRECTORY_SEPARATOR . 'Ebh');
//设置应用路径
Ebh()->loader->setApplicationDir('Ebh');

$api = new EbhService();

$result = $api->response();

var_dump($result);
//$result->output();

