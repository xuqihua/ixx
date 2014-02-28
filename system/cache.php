<?php
//简单的缓存

class Cache {
	
	public static $path = '';
	
	
	public static _make_directory($key) {
		$mkey = md5($key);
		$path = ROOT.'data'.DS.$mkey[0].DS;
		if(!is_dir($path)) {
			mkdir($path,0755,true);
		}
		return $path.$mkey;
	}
	public static function set($key, $value, $ttl = 300) {
		$file = self::_make_directory($key);
		if(!is_file($file)) {
			
		}
	}
	
	public static function get($key) {
		
		
	}
	
	public static function del($key) {
		
		
	}
}