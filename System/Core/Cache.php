<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 缓存接口
 */
interface Cache{
    /**
     * 设置缓存
     * @param $key 缓存key
     * @param $value 缓存内容
     * @param int $expire 缓存有效时间
     * @return mixed
     */
    public function set($key,$value,$expire = 600);

    /**
     * 读取缓存
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * 删除缓存
     * @param $key
     * @return mixed
     */
    public function delete($key);
}