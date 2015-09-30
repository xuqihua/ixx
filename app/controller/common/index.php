<?php 
namespace app\controller\common;
//默认首页
class index extends \Controller {
	
	public $restful = true;
	public function get_index() {
		echo 'hello world!';
	}
}