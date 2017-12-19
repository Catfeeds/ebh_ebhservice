<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 * 日志抽象类
 *
 * 对系统的日志进行记录,具体存储媒介 可以自定义实现
 *
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
abstract class Log {
    /**
     * @var int $logLevel 多个日记级别
     */
    protected $logLevel = 0;


    /**
     * @var int LOG_LEVEL_DEBUG 调试级别
     */
    const LOG_LEVEL_DEBUG = 1;

    /**
     * @var int LOG_LEVEL_INFO 产品级别
     */
    const LOG_LEVEL_INFO = 2;

    /**
     * @var int LOG_LEVEL_ERROR 错误级别
     */
    const LOG_LEVEL_ERROR = 4;


    public function __construct($level) {
        $this->logLevel = $level;
    }

    /**
     * 记录日志 自行实现
     * @param $type
     * @param $msg
     * @param $data
     * @return mixed
     */
    abstract public function log($type, $msg, $data);


    /**
     * 应用产品级日记
     * @param string $msg 日记关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    public function info($msg, $data = NULL) {
        if (!$this->isAllowToLog(self::LOG_LEVEL_INFO)) {
            return;
        }

        $this->log('info', $msg, $data);
    }


    /**
     * 开发调试级日记
     * @param string $msg 日记关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    public function debug($msg, $data = NULL) {
        if (!$this->isAllowToLog(self::LOG_LEVEL_DEBUG)) {
            return;
        }

        $this->log('debug', $msg, $data);
    }

    /**
     * 系统错误级日记
     * @param string $msg 日记关键描述
     * @param string/array $data 场景上下文信息
     * @return NULL
     */
    public function error($msg, $data = NULL) {
        if (!$this->isAllowToLog(self::LOG_LEVEL_ERROR)) {
            return;
        }

        $this->log('error', $msg, $data);
    }



    /**
     * 是否允许写入日记，或运算
     * @param int $logLevel
     * @return boolean
     */
    protected function isAllowToLog($logLevel) {
        return (($this->logLevel & $logLevel) != 0) ? TRUE : FALSE;
    }
}