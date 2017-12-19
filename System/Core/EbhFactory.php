<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 创建控制器类
 * 根据请求(?service=XXX.XXX)生成对应的接口服务，并进行初始化
 *
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class EbhFactory {
    public static $actionName = '';
    public static $className = '';
    /**
     * 根据客户端请求服务名和方法创建控制器
     * 如果创建失败 则抛出异常
     * @param bool $isInitialize
     */
    static function generateService($isInitialize = TRUE) {
        Ebh()->url->parseUrl();
        $directory = Ebh()->url->getDirectory();
        $path = APP_PATH . DIRECTORY_SEPARATOR . 'Controller';
        if(!empty($directory)){
            $path .=  DIRECTORY_SEPARATOR . $directory;
        }
        $service = Ebh()->url->getControl().'.'.Ebh()->url->getMethod();

        $serArr = explode('.',$service);

        if (count($serArr) < 2) {
            throw new Exception_BadRequest('service '.$service.' illegal');

        }


        list ($className, $action) = $serArr;

        if($className == '' || $action == ''){
            throw new Exception_BadRequest('service '.$service.' illegal');
        }

        $path .= DIRECTORY_SEPARATOR . Ebh()->url->getControl() . '.php';

        $className =  ucfirst($className);
        $className .= 'Controller';
        $action .='Action';
        self::$className = $className;
        self::$actionName = $action;
        if(!file_exists($path)){
            throw new Exception_BadRequest('no such file as '.$path);
        }
        if (!class_exists($className)) {
            require_once $path;
        }
        $api = new $className();
        if (!method_exists($api, $action) || !is_callable(array($api, $action))) {
            throw new Exception_BadRequest('no such service as '.$service);
        }

        if ($isInitialize) {
            $api->init();
        }

        return $api;
    }


}