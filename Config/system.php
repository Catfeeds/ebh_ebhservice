    <?php
/**
 * ebhservice.
 * Author: jw
 * Email: 345468755@qq.com
 */
return array(
    'debug' => true,
    'cache' => array(
        'file' => array(
            'path' => 'Runtime',
            'prefix' => 'ebh_'
        ) ,
        'memcache' => array(
            'host' => '192.168.0.24',
            'port' => 11200,
            'prefix' => 'ebh_'
        ) ,
        'redis' => array(
            'host' => '192.168.0.24',
            'port' => 6379,
            'prefix' => 'ebh_'
        )
    ) ,
		
	//默认加载的工具包	
    'auto_helper' => array(
        'common'
    ) ,
    //分页配置
    'page' => array(
        'var_page' => 'p', //分页参数
        'listRows' => 20
        //每页加载条数
        
    ) ,
    'route' => array(
        'suffix' => '', //路径后缀
        
    ) ,
    'security'=>array('authkey'=>'SFDSEFDSDF'),
);

