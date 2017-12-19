<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 9:14
 * 第三方登录驱动类
 */
class OtherLogin{


    /**
     * 获取一个实例
     * @param $type
     * @param $openId
     * @return mixed
     * @throws Exception_BadRequest
     */
    public static function getObj($type,$openId,$parameters){
        $className = ucfirst($type);
        $filePath = APP_PATH . DIRECTORY_SEPARATOR . 'Libs' . DIRECTORY_SEPARATOR . 'otherlogin' . DIRECTORY_SEPARATOR . $className . '.php';

        if(!file_exists($filePath)){
            throw new Exception_BadRequest('当前登录方式不存在');
        }
        if(!class_exists($className)){
            require_once $filePath;
        }




        $obj = new $className($openId,$parameters);

        return $obj;
    }



}


/**
 * 定义第三方登录的接口类
 * Interface OtherLoginInterface
 */
interface OtherLoginInterface{
    function __construct($openId,$parameters);
    function getUser();

    /**
     * 绑定用户
     * @param $uid
     * @return mixed
     */
    function bindUser($uid);
    //用于返回错误信息
    function getErr();
}