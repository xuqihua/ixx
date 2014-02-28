<?php
//initialize
error_reporting(E_ALL); //上线改为0;
define('DS', DIRECTORY_SEPARATOR); //重新定义目录切割符号
define('ROOT', realpath(dirname(__FILE__)).DS); //程序根目录
define('EXT','.php'); //定义类文件的后缀名
define('APP_PATH',ROOT.'app'.DS);
define('CHARSET','UTF-8');

require ROOT.'system/core.php';
require ROOT.'system/helpers.php';

//设置自动加载的目录
Core::$directories = array(
	'system' => ROOT.'system'.DS,
	'app' => ROOT.'app'.DS,
);
spl_autoload_register(array('Core', 'auto_load'));

//设置程序配置文件目录
Config::$path = APP_PATH.'config'.DS;
View::$path = APP_PATH.'view'.DS;
//运行
header('Content-type: text/html; charset=utf-8');
//header("cache-control:no-cache,must-revalidate");
//设置路由
Route::set('default', '(<directory>)(/<controller>(/<action>))', array('directory' => '(admin|common)'))
	->defaults(array(
		'directory' => 'common',
		'controller' => 'index',
		'action'     => 'index',
	));
Core::dispatch();