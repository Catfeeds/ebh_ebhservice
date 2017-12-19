<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Cache_File implements Cache{

    protected $folder;
    protected $prefix;
    public function __construct($config) {
        $this->folder = rtrim($config['path'],'/');
        $cacheFolder = $this->createCacheFileFolder();

        if(!is_dir($cacheFolder)){
            mkdir($cacheFolder,0777,true);
        }

        $this->prefix = isset($config['prefix']) ? $config['prefix'] : 'ebh_';

    }

    public function createCacheFileFolder(){
        return $this->folder . DIRECTORY_SEPARATOR . 'cache';
    }
    public function set($key,$value,$expire = 600){
        if ($key === NULL || $key === '') {
            return;
        }
        $key = $this->formatKey($key);
        $filePath = $this->createCacheFilePath($key);

        $expireStr = sprintf('%010d', $expire + time());

        if (strlen($expireStr) > 10) {
            throw new Exception_InternalServerError('file expire is too large');
        }

        if(!file_exists($filePath)){
            touch($filePath);
            chmod($filePath, 0777);
        }
        file_put_contents($filePath, $expireStr . serialize($value));

    }
    public function get($key){
        $key = $this->formatKey($key);
        $filePath = $this->createCacheFilePath($key);

        if (file_exists($filePath)) {
            $expireTime = file_get_contents($filePath, FALSE, NULL, 0, 10);

            if ($expireTime > time()) {
                return @unserialize(file_get_contents($filePath, FALSE, NULL, 10));
            }
        }

        return NULL;
    }
    public function delete($key){
        if ($key === NULL || $key === '') {
            return;
        }
        $key = $this->formatKey($key);
        $filePath = $this->createCacheFilePath($key);

        @unlink($filePath);
    }

    /**
     * linux中同一个目录下有文件个数限制，这里拆分1000个文件缓存目录
     * @param $key
     * @return string
     */
    public function createCacheFilePath($key){
        $folderSufix = sprintf('%03d', hexdec(substr(sha1($key), -5)) % 1000);
        $cacheFolder = $this->createCacheFileFolder() . DIRECTORY_SEPARATOR . $folderSufix;
        if (!is_dir($cacheFolder)) {
            mkdir($cacheFolder, 0777, TRUE);
        }

        return $cacheFolder . DIRECTORY_SEPARATOR . md5($key) . '.dat';
    }



    protected function formatKey($key){
        return $this->prefix . $key;
    }
}