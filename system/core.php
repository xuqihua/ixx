<?php 
/*
 * xuqihua @12:08 2013/10/26
 *
 */
 

class Core {
	
	public static $directories = array();
	public static $default_directory = 'system';
	public static $uri;
	public static $_params;
	public static $method;
	
	public static function auto_load($class, $directory = null) {
		$file = str_replace('_', DS, strtolower($class));
		if($directory !== null && isset(self::$directories[$directory])) {
			$path = self::$directories[$directory].$file.EXT;
			if(is_file($path)) {
				require $path;
				return true;
			} else {
				show_error();
				return false;
			}
		}
		if(!empty(self::$directories)) {
			foreach(self::$directories as $directory) {
				$path = $directory.$file.EXT;
				if(is_file($path)) {
					require $path;
					return true;
				}
			}
		}
		show_error();
		return false;
	}
	
	public static function get_uri() {
		if(!isset(self::$uri)) {
			if ( ! empty($_SERVER['PATH_INFO'])) {
				self::$uri = $_SERVER['PATH_INFO'];
			} else {
				if (isset($_SERVER['REQUEST_URI'])) {
					self::$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
					self::$uri = rawurldecode(self::$uri);
				} elseif (isset($_SERVER['PHP_SELF'])) {
					self::$uri = $_SERVER['PHP_SELF'];
				} elseif (isset($_SERVER['REDIRECT_URL'])) {
					self::$uri = $_SERVER['REDIRECT_URL'];
				} else {
					exit('Unable to detect the URI using PATH_INFO, REQUEST_URI, PHP_SELF or REDIRECT_URL');
				}
			}
		}
		return self::$uri;
	}
	
	public static function detect_uri() {
		$GET = array();
		$uri = trim(self::get_uri(), '/');
		if (empty($uri) || $uri == 'index.php') {
			$GET['directory'] = 'common';
			$GET['controller'] = 'index';
			$GET['action'] = 'index';
			return $GET;
		} else {
			$routes = Route::all();
			foreach ($routes as $route) {
				if ($GET = $route->matches($uri)) {
					return $GET;
				}
			}
			show_error();
		}
	}
	
	public static function method() {
		if(!isset(self::$method)) {
			$req_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
			if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
				$req_method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
			} else if (isset($_REQUEST['_method'])) {
				$req_method = $_REQUEST['_method'];
			}
			self::$method = $req_method;
		}
		return self::$method;
	}
	
	public static function dispatch() {
		$uri = Core::detect_uri();
		self::$_params = $uri;
		$class_name = 'Controller_';
		if(!empty($uri['directory'])) {
            $class_name .= $uri['directory'].'_';
		}
        $class_name .= $uri['controller'];
		$class = new $class_name;
		$action = 'action_'.$uri['action'];
		if($class->restful == true) {
			$action = strtolower(self::method()).'_'.$uri['action'];
		}
		$class->before();
		if(method_exists($class,$action)) {
			$class->{$action}();
		} else {
			$class->reload($action);
		}
		$class->after();
	}
}