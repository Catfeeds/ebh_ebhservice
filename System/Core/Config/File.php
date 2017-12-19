<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 文件配置类
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Config_File implements Config{
    /**
     * @var string $path 配置文件的目录位置
     */
    private $path = '';

    /**
     * @var array $map 配置文件的映射表，避免重复加载
     */
    private $map = array();

    public function __construct($configPath) {
        $this->path = $configPath;
    }

    /**
     * 获取配置
     * @param $key
     * @param null $default
     * @return null
     */
    public function get($key, $default = NULL) {
        $keyArr = explode('.', $key);
        $fileName = $keyArr[0];
        if (!isset($this->map[$fileName])) {
            $this->loadConfig($fileName);
        }

        $rs = NULL;
        $preRs = $this->map;
        foreach ($keyArr as $subKey) {
            if (!isset($preRs[$subKey])) {
                $rs = NULL;
                break;
            }
            $rs = $preRs[$subKey];
            $preRs = $rs;
        }

        return $rs !== NULL ? $rs : $default;
    }


    /**
     * 加载配置文件
     * @param $fileName
     */
    private function loadConfig($fileName) {
        $config = include($this->path . DIRECTORY_SEPARATOR . $fileName . '.php');

        $this->map[$fileName] = $config;
    }
}