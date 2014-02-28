<?php

return array(
	'url' => '/',  #配置首页的地址
	'encoding' => 'UTF-8',  #编码
	'database' => array( #数据库配置
		'driver'   => 'mysql',
		'host'     => 'localhost',
		'database' => 'test',
		'username' => 'root', 
		'password' => '',
		'charset'  => 'utf8',
		'prefix'   => 'prefix__',
	),
	'route' => array(
		'type' => 'pathinfo', #pathinfo rewrite normal
		'prefix' => '',
	),
	'view' => array( #模板配置
		
	),
);
