<?php
//简单的缓存

class Cache {
	
	public static $path = '';
	
	
	public static function _make_directory($key) {
		$hash_key = md5($key);
		$path = ROOT.'data'.DS.$hash_key[0].DS;
		if(!is_dir($path)) {
			mkdir($path,0755,true);
		}
		return $path.$hash_key;
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