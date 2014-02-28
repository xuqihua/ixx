<?php 
//设计只支持一级配置

class Config {
	public static $path;
	public static $items = array();
	public static function get($key, $default = null) {
		$keys = explode('.',$key);
		$filename = array_shift($keys);
		if(isset(self::$items[$filename])) {
			$configs = self::$items[$filename];
		} else {
			$file = self::$path.$filename.EXT;
			if(!is_file($file)) {
				return $default;
			}
			$configs = include $file;
		}
		$value = $configs;
		foreach ($keys as $segment){
			if ( ! is_array($value) or ! array_key_exists($segment, $value)){
				return $default;
			}
			$value = $value[$segment];
		}
		self::$items[$filename] = $configs;
		return $value;
	}
	
	//保存
	public static function save($filename,$array) {
		if(!is_array($array)) return false;
		$file = self::$path.$filename.EXT;
		$array = "<?php\nreturn ".var_export($array, true).";";
		$strlen = file_put_contents($file, $array);
		@chmod($file, 0755);
		return $strlen;
	}
}