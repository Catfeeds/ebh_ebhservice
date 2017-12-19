<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
defined('EBH_ROOT') OR exit('No direct script access allowed');
class Log_File extends Log{

    protected $logFolder;
    protected $dateFormat;

    protected $fileDate;
    protected $logFile;
    public function __construct($logFolder, $level, $dateFormat = 'Y-m-d H:i:s') {
        $this->logFolder = $logFolder;
        $this->dateFormat = $dateFormat;

        parent::__construct($level);

        $this->init();
    }

    protected function init() {
        $curFileDate = date('Y-m-d', time());
        if ($this->fileDate == $curFileDate) {
            return;
        }
        $this->fileDate = $curFileDate;

        $folder = $this->logFolder
            . DIRECTORY_SEPARATOR . 'log'
            . DIRECTORY_SEPARATOR . substr($this->fileDate, 0, -3);
        if (!file_exists($folder)) {
            mkdir($folder . '/', 0777, TRUE);
        }

        // 每天一个文件
        $this->logFile = $folder
            . DIRECTORY_SEPARATOR . $this->fileDate . '.log';
        if (!file_exists($this->logFile)) {
            touch($this->logFile);
            chmod($this->logFile, 0777);
        }
    }

	/**
	 * debug模式下开启trace信息,其他仅日志写入
	 * {@inheritDoc}
	 * @see Log::log()
	 */
    public function log($type, $msg, $data) {
    	$content = '';//日志拼接字符串
        $this->init();
       	$content .= date($this->dateFormat, time()).'|';
        $content .= strtoupper($type). " --->" . PHP_EOL;
        $content .= str_replace(PHP_EOL, "\n", $msg) . PHP_EOL;
        if ($data !== NULL) {
          	$content .= is_array($data) ? var_export($data,true) : $data;
        }
        if(strtolower($type)== 'debug') {	//添加PHP调用堆栈信息
        	$message = "\r\n".'trace:'."\n";
        	$infolines = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        	foreach($infolines as $infokey=>$infoline) {
        		if(!empty($infoline['function']) && !empty($infoline['file']) && !empty($infoline['line']) ){
        			$linemsg = $infoline['function'].'() '.$infoline['file'].':'.$infoline['line']."\n";
        			$message .= $linemsg;
        		}
        	}
        	$message .= 'REQUEST_URI '.$_SERVER['REQUEST_URI']."\n";
        	$content .= $message;
        }
        $content .= PHP_EOL . PHP_EOL;
        file_put_contents($this->logFile, $content, FILE_APPEND);
    }
}