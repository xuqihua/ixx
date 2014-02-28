<?php 


class Controller {
	public $restful = false; //是否restfull
	
	protected function view() {
		
	}
	
	public function before() {
		
	}
	
	public function after() {
		
	}
	public function redirect($url,$msg = '') {
		$plus = '';
		if(!empty($msg)) {
			$plus = 'alert("'.$msg.'");';
		}
		if($url == '-1') {
			$re = "window.history.back();";
		} else {
			$re = 'window.location.href="'.Config::get('global.url').$url.'"';
		}
		echo '<script>'.$plus.$re.'</script>';
		exit;
	}
	
	public function reload($action = '') {
		show_error();
	}
}