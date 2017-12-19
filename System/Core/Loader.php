<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 自动加载Loader
 *
 * 按类名映射文件路径自动加载类文件
 * 可以自定义加载指定文件
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Ebh_Loader {
    /**
     * @var array
     * 指定需要自动加载的路径
     */
    protected $dirs = array();

    /**
     * @var string
     * 需要加载的根目录
     */
    protected $basePath = '';


    /**
     * 初始化Loader时传入根目录
     * @param $basePath
     * @param array $dirs
     */
    public function __construct($basePath, $dirs = array()) {

        $this->setBasePath($basePath);
        if (!empty($dirs)) {
            $this->addDirs($dirs);
        }

        spl_autoload_register(array($this, 'load'));
    }


    /**
     * 增加需要自动加载的目录
     * @param $dirs
     */
    public function addDirs($dirs) {
        if(!is_array($dirs)) {
            $dirs = array($dirs);
        }

        $this->dirs = array_merge($this->dirs, $dirs);
    }


    /**
     * 设置根目录
     * @param $path
     */
    public function setBasePath($path) {
        $this->basePath = $path;
    }

    /**
     * 设置应用路径
     * @param $dir
     */
    public function setApplicationDir($dir){
        $sub_path = array('Model','Libs');
        foreach($sub_path as $path){
            $this->addDirs($dir.DIRECTORY_SEPARATOR.$path);
        }
    }




    public function load($className) {
        if (class_exists($className, FALSE) || interface_exists($className, FALSE)) {
            return;
        }
        //如果加载的class结尾是Controller  并且classname 不等于Controller 去掉Controller
        if(substr($className,-10) == 'Controller' && $className != 'Controller'){
            $className = substr($className,0,strlen($className)-10);
        }

        if ($this->loadClass(EBHSERVICE_ROOT, $className)) {
            return;
        }

        if ($this->loadClass(EBHSERVICE_ROOT.DIRECTORY_SEPARATOR.'Core', $className)) {
            return;
        }

        if ($this->loadClass(EBHSERVICE_ROOT.DIRECTORY_SEPARATOR.'Library', $className)) {
            return;
        }

        foreach ($this->dirs as $dir) {
            if ($this->loadClass($this->basePath . DIRECTORY_SEPARATOR . $dir, $className)) {
                return;
            }
        }
    }


    protected function loadClass($path, $className) {
        $toRequireFile = $path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists($toRequireFile)) {

            require_once $toRequireFile;
            return TRUE;
        }

        return FALSE;
    }
}