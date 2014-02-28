<?php 

//默认首页
class Controller_Common_index extends Controller {
	
	public $restful = true;
	public function get_index() {
		echo 'hello world!';
	}
}