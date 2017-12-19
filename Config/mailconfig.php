<?php
/**
 * ebhservice.
 * User: jiangwei
 * Email: 345468755@qq.com
 * Time: 13:20
 */
return array(
    'mailsend' 			=>  2,
    'maildelimiter' 	=> '1',						//邮件头的分隔符，1:\r\n   2:\r   3:\n
    'mailusername'		=> '1',						//收件人地址中是否包含账号
    'server' 			=> 'smtp.163.com',			//SMTP 服务器
    'port' 				=> '25',					//SMTP 端口, 默认不需修改
    'auth' 				=> '1',						//是否需要 AUTH LOGIN 验证, 1=是, 0=否
    'from' 				=> '15968119644@163.com',	//发信人地址 (如果需要验证,必须为本服务器地址)
    'auth_username'		=> '15968119644@163.com',	//验证账号
    'auth_password'	 	=> '977518',
    'adminemail'		=> 'support@51ebh.com',
    'sitename'			=> 'e板会',
    'ver'				=> '1.0',
);