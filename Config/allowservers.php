<?php
/*
 * 允许上传的服务器IP列表，一般需要服务器内部调用服务时才需要用到这个做判断。
 * 支持 * 号通配符
 */
return array(
    '192.168.0.200',
	'192.168.0.24',
	'192.168.0.27',
	'127.0.0.*',
    '192.168.*.*',
);