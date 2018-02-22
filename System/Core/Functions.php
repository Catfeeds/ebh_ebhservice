<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */


function isCli(){
    return preg_match("/cli/i", php_sapi_name()) ? true : false;
}
/**
 * 读取Ebh实例
 *
 * @return Ebh
 */

if(!function_exists('Ebh')){
    function Ebh(){
        return Ebh::getInstance();
    }
}


/**
 * 获取配置
 * @param $key
 * @return mixed
 */
function getConfig($key){
    return Ebh()->config->get($key);
}




/**
 * 错误处理方法
 * @param $error_level
 * @param $error_message
 * @param $error_file
 * @param $error_line
 * @param $error_context
 */
function _error_handler($error_level,$error_message,$error_file,$error_line,$error_context){
    $uri = $_SERVER['REQUEST_URI'];
    $data = array();
    //Ebh()->log->error ("error_level:$error_level error_message:$error_message error_file:$error_file error_line:$error_line uri:$uri");
    $data['error_level'] = $error_level;
    $data['error_message'] = $error_message;
    $data['error_file'] = $error_file;
    $data['error_line'] = $error_line;
    $data['error_context'] = $error_context;
    $data['url'] = $uri;
	//log_message($error_message,'error');
    Ebh()->log->error ($error_message,$data);
}

function _shutdown_handler(){
    $uri = $_SERVER['REQUEST_URI'];
    $data = error_get_last();
    if(!empty($data)){
        $data['url'] = $uri;
        Ebh()->log->error ('shotdownError:',$data);
    }

}

/**
 * php版本低于php5.5array_column
 */
if(function_exists('array_column') === false) {
    function array_column($arr, $column_name) {
        $tmp = array();
        foreach($arr as $item) {
            $tmp[] = $item[$column_name];
        }
        return $tmp;
    }
}
/**
 * 切换数据库
 * @param $dbname 数据库配置文件名
 * @return mixed
 */
function getOtherDb($dbname) {
    if (!Ebh()->get($dbname)) {
        $db = new Db(Ebh()->config->get($dbname));
        Ebh()->set($dbname, $db);
    }
    return Ebh()->get($dbname);
}