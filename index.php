<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */


//加载初始化文件
defined('APP_PATH') || define('APP_PATH', dirname(__FILE__). DIRECTORY_SEPARATOR . 'Ebh');
defined('IS_CLI') || define('IS_CLI', preg_match("/cli/i", php_sapi_name()) ? true : false);
//转化CLI模式的参数
if(IS_CLI){
    $param = getopt('u:p:');
    $_SERVER['REQUEST_URI'] = isset($param['u']) ? $param['u'] : '';

    $params = isset($param['p']) ? urldecode($param['p']) : array();
    if(!empty($params)){
        parse_str($params,$params);
    }
    $_REQUEST = array_merge($_REQUEST,$params);

}
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .'init.php';
//设置应用路径
Ebh()->loader->setApplicationDir('Ebh');

$api = new EbhService();

$result = $api->response();

$result->output();

