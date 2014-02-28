<?php 

class Route {

	const REGEX_KEY     = '<([a-zA-Z0-9_]++)>';
	const REGEX_SEGMENT = '[^/.,;?\n]++';
	const REGEX_ESCAPE  = '[.\\+*?[^\\]${}=!|]';
	
	public static $default_action = 'index';
	protected static $_routes = array();
	public static function set($name, $uri, array $regex = NULL) {
		return Route::$_routes[$name] = new Route($uri, $regex);
	}

	public static function get($name) {
		if ( ! isset(Route::$_routes[$name])) {
			return false;
		}
		return Route::$_routes[$name];
	}

	public static function all() {
		return Route::$_routes;
	}

	public static function name(Route $route) {
		return array_search($route, Route::$_routes);
	}

	public static function cache($save = FALSE) {
		
	}
	
	public static function clear() {
		return Route::$_routes = array();
	}
	
	public static function url($name, array $params = NULL, $protocol = NULL) {
		//return URL::site(Route::get($name)->uri($params), $protocol);
		return Route::get($name)->uri($params);
	}

	protected $_uri = '';
	protected $_regex = array();
	protected $_defaults = array('controller' => 'index','action' => 'index');
	protected $_route_regex;

	public function __construct($uri = NULL, array $regex = NULL) {
		if ($uri === NULL) {
			return;
		}

		if ( ! empty($regex)) {
			$this->_regex = $regex;
		}

		$this->_uri = $uri;
		$this->_route_regex = $this->_compile();
	}

	public function defaults(array $defaults = NULL) {
		$this->_defaults = $defaults;
		return $this;
	}

	public function matches($uri) {
		if ( ! preg_match($this->_route_regex, $uri, $matches)) {
			return FALSE;
		}

		$params = array();
		foreach ($matches as $key => $value) {
			if (is_int($key)) {
				continue;
			}
			$params[$key] = $value;
		}

		foreach ($this->_defaults as $key => $value) {
			if ( ! isset($params[$key]) OR $params[$key] === '') {
				$params[$key] = $value;
			}
		}

		return $params;
	}

	public function uri(array $params = NULL) {
		if ($params === NULL) {
			$params = $this->_defaults;
		} else {
			$params += $this->_defaults;
		}

		$uri = $this->_uri;
		if (strpos($uri, '<') === FALSE AND strpos($uri, '(') === FALSE) {
			return $uri;
		}

		while (preg_match('#\([^()]++\)#', $uri, $match)) {
			$search = $match[0];
			$replace = substr($match[0], 1, -1);

			while(preg_match('#'.Route::REGEX_KEY.'#', $replace, $match)) {
				list($key, $param) = $match;

				if (isset($params[$param])) {
					$replace = str_replace($key, $params[$param], $replace);
				} else {
					$replace = '';
					break;
				}
			}

			$uri = str_replace($search, $replace, $uri);
		}

		while(preg_match('#'.Route::REGEX_KEY.'#', $uri, $match)) {
			list($key, $param) = $match;

			if ( ! isset($params[$param])) {
				return false;
			}

			$uri = str_replace($key, $params[$param], $uri);
		}

		$uri = preg_replace('#//+#', '/', rtrim($uri, '/'));

		return $uri;
	}

	protected function _compile() {
		$regex = preg_replace('#'.Route::REGEX_ESCAPE.'#', '\\\\$0', $this->_uri);

		if (strpos($regex, '(') !== FALSE) {
			$regex = str_replace(array('(', ')'), array('(?:', ')?'), $regex);
		}

		$regex = str_replace(array('<', '>'), array('(?P<', '>'.Route::REGEX_SEGMENT.')'), $regex);

		if ( ! empty($this->_regex)) {
			$search = $replace = array();
			foreach ($this->_regex as $key => $value) {
				$search[]  = "<$key>".Route::REGEX_SEGMENT;
				$replace[] = "<$key>$value";
			}
			$regex = str_replace($search, $replace, $regex);
		}
		return '#^'.$regex.'$#uD';
	}
}
