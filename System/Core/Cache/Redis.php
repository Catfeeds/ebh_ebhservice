<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Cache_Redis implements Cache{

    protected $redis;

    protected $auth;

    protected $prefix;
    public function __construct($config) {
        $this->redis = new Redis();
        //连接
        $port = isset($config['port']) ? intval($config['port']) : 6379;
        $timeout = isset($config['timeout']) ? intval($config['timeout']) : 300;
        $this->redis->connect($config['host'], $port, $timeout);
        //验证
        $this->auth = isset($config['auth']) ? $config['auth'] : '';
        if ($this->auth != '') {
            $this->redis->auth($this->auth);
        }

        //选择库

        $dbIndex = isset($config['db']) ? intval($config['db']) : 0;
        $this->redis->select($dbIndex);

        $this->prefix = isset($config['prefix']) ? $config['prefix'] : 'ebh_';
    }

    public function set($key, $value, $expire = 600) {
        $this->redis->setex($this->formatKey($key), $expire, $this->formatValue($value));
    }

    public function get($key) {
        $value = $this->redis->get($this->formatKey($key));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    public function delete($key) {
        return $this->redis->delete($this->formatKey($key));
    }

    protected function unformatValue($value) {
        return @unserialize($value);
    }
    protected function formatValue($value) {
        return @serialize($value);
    }

    protected function formatKey($key) {
        return $this->prefix . $key;
    }

    /**
     * 直接返回redits实例
     * @return Redis
     */
    public function getRedis(){
        return $this->redis;
    }

    public function hIncrBy($name, $key, $num = 1){
        return $this->redis->hIncrBy($this->formatKey($name), $key, $num);
    }
     /**
     *    set hash opeation
     */
    public function hSet($name,$key,$value) {
        return $this->redis->hset($this->formatKey($name),$key,$this->formatValue($value));   
    }

    /**
     *    get hash opeation
     */
    public function hGet($name,$key = null, $unserializeable = true) {
        if($key){
            $value = $this->redis->hget($this->formatKey($name),$key);
        } else {
            $value = $this->redis->hgetAll($this->formatKey($name));
        }
        if (empty($value)) {
            return '';
        }
        if (empty($unserializeable)) {
            return $value;
        }
        return $this->unformatValue($value);
    }
	
	/**
     *    delete hash opeation
     */
    public function hDel($name,$key = null){
        if($key){
            return $this->redis->hdel($this->formatKey($name),$key);
        }
        return $this->redis->delete($this->formatKey($name));
    }
	
    /*******************************************************
    队列操作开始 start 通过list模拟队列queue操作
    ********************************************************/
    /**
     * 入列
     * @param $queueName string 队列名称
     * @param $value object 入列元素的值
     */
    public function qPush($queueName,$value) {
        return $this->redis->rpush($this->formatKey($queueName),$this->formatValue($value));
    }
    /**
     * 出列
     * @param $queueNam
     */
    public function qPop($queueName) {
        $value = $this->redis->lpop($this->formatKey($queueName));
        if (!empty($value))
            $value = $this->unformatValue($value);
        return $value;
    }
    /**
     * 获取队列长度
     */
    public function qLen($queueName) {
        return $this->redis->llen($this->formatKey($queueName));
    }
    /*******************************************************
    队列操作结束 end 
    ********************************************************/
}