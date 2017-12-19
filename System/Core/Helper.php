<?php
defined('EBH_ROOT') OR exit('No direct script access allowed');
defined('HELPER_PATH') || define('HELPER_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'Helper'.DIRECTORY_SEPARATOR);
class Helper {
	
	private $_helpers = array();    //已加载辅助方法库
	
	public function __construct(){
		$this->auto_helper = Ebh()->config->get('system.auto_helper');
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	private function  init(){
		//加载helper
		foreach ($this->auto_helper as $helper) {
			$this->load($helper);
		}
	}
	
	/**
	 * 加载辅助方法
	 * @param string $helpername 辅助方法库方法
	 */
	public function load($helpername){
		if (!isset($this->_helpers[$helpername])) {
            //先从系统目录查找
		    if(file_exists(HELPER_PATH . $helpername . '.php')){
                require_once (HELPER_PATH . $helpername . '.php');
                $this->_helpers[$helpername] = TRUE;
                return TRUE;
            }

            //如果系统目录找不到 从应用目录查找

            if(file_exists(APP_PATH.DIRECTORY_SEPARATOR.'Helper'.DIRECTORY_SEPARATOR.$helpername.'.php')){
                require_once (APP_PATH.DIRECTORY_SEPARATOR.'Helper'.DIRECTORY_SEPARATOR.$helpername.'.php');
                $this->_helpers[$helpername] = TRUE;
                return TRUE;
            }

		}



        return TRUE;
	}

}