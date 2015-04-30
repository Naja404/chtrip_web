<?php
/**
 * api index
 *
 */
namespace Api\Controller;
use Think\Controller;

class IndexController extends Controller {

    protected function _initialize(){
    	$this->response = array(
    			'state' => 0,
    			'data' => '',
    		);
    }

    public function index(){
    	$this->ajaxReturn($this->response);
    }

}
