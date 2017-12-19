<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */

//定义框架版本
defined('EBHSERVICE_VERSION') || define('EBHSERVICE_VERSION', '1.0.0');
//定义框架目录
defined('EBHSERVICE_ROOT') || define('EBHSERVICE_ROOT', dirname(__FILE__));





//加载Loader
require_once EBHSERVICE_ROOT . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Loader.php';
require_once EBHSERVICE_ROOT . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Functions.php';

set_error_handler('_error_handler');
register_shutdown_function('_shutdown_handler');


/**
 * Class EbhService
 * Ebh应用Service
 *
 * 用于实现应用的 响应和调用等操作
 *
 * usage:
 * $api =  new EbhService();
 * $result = $api->response();
 * $result->output();
 */
class EbhService {



    /**
     *
     * 响应远程请求
     *
     * 创建合适的控制器->方法 最后返回数据
     *
     */
    public function response() {
        $result = Ebh()->response;
        try{
            $api = EbhFactory::generateService();
            $data = call_user_func(array($api, EbhFactory::$actionName));
            $result->setData($data);
        }catch (EbhException $ex) {
            // 框架或项目的异常
            $result->setRet($ex->getCode());
            $result->setMsg($ex->getMessage());
        }catch (Exception $ex) {
            // 不可控的异常
            Ebh()->log->error($_SERVER['REQUEST_URI'], strval($ex));
            throw $ex;
        }


        return $result;

    }
}

