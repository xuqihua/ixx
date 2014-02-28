<?php 


class View {
	
	public $view; #模板
	public $data; #变量
	public static $path; #路径
	
	public function __construct($view, $data = array()) {
		$this->view = self::$path.$view.EXT;
		$this->data = $data;
	}
	public static function make($view,$data = array()) {
		return new View($view, $data);
	}
	
	public function with($key, $value = null) {
		if (is_array($key)){
			$this->data = array_merge($this->data, $key);
		} else {
			$this->data[$key] = $value;
		}
		return $this;
	}
	
	public function get() {
		$__data = $this->data;
		ob_start() and extract($__data, EXTR_SKIP);
		include $this->view;
		$content = ob_get_clean();
		return $content;
	}
	
	public function render() {
		echo $this->get();
	}
}