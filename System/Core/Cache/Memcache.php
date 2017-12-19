<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Cache_Memcache implements Cache{
    protected $memcache = null;

    protected $prefix;

    /**
     * @param string $config['host'] Memcache域名
     * @param int $config['port'] Memcache端口
     * @param string $config['prefix'] Memcache key prefix
     */
    public function __construct($config) {
        $this->memcache = $this->createMemcache();
        $this->memcache->addServer($config['host'], $config['port']);
        $this->prefix = isset($config['prefix']) ? $config['prefix'] : 'ebh_';
    }

    public function set($key, $value, $expire = 600) {
        if ($key === NULL || $key === '') {
            return;
        }
        $this->memcache->set($this->formatKey($key), @serialize($value), 0, $expire);
    }

    public function get($key) {
        $value = $this->memcache->get($this->formatKey($key));
        return $value !== FALSE ? @unserialize($value) : NULL;
    }

    public function delete($key) {
        if ($key === NULL || $key === '') {
            return;
        }
        return $this->memcache->delete($this->formatKey($key));

    }


    protected function createMemcache() {
        return new Memcache();
    }

    protected function formatKey($key) {
        return $this->prefix . $key;
    }
}