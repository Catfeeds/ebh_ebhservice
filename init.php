<?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
date_default_timezone_set('Asia/Shanghai');
defined('EBH_ROOT') || define('EBH_ROOT', dirname(__FILE__));
defined('SYSTIME') || define('SYSTIME', time());

require_once EBH_ROOT . '/System/EbhService.php';
$loader = new Ebh_Loader(EBH_ROOT, array());

/** ---------------- 注册&初始化 基本服务组件 ---------------- **/

Ebh()->loader = $loader;

//注册配置文件服务
Ebh()->config = new Config_File(EBH_ROOT . DIRECTORY_SEPARATOR . 'Config');

//读取调试模式
Ebh()->debug = Ebh()->config->get('system.debug');


//注册日志服务
Ebh()->log = new Log_File(EBH_ROOT . DIRECTORY_SEPARATOR . 'Runtime', Log::LOG_LEVEL_DEBUG | Log::LOG_LEVEL_INFO | Log::LOG_LEVEL_ERROR);

//注册数据库服务
Ebh()->db = new Db(Ebh()->config->get('db'));

//注册缓存服务
Ebh()->cache = new Cache_Redis(Ebh()->config->get('system.cache.redis'));

//注册URL解析服务
Ebh()->url = new Url();

//注册签名拦截器
Ebh()->filter = 'Filter_Server';

//注册工具方法
Ebh()->helper = new Helper();


//注册sns数据库服务
Ebh()->snsdb = new Db(Ebh()->config->get('snsdb'));