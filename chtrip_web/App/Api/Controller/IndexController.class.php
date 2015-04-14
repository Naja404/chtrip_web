<?php
/**
 * api index
 *
 */
namespace Api\Controller;
use Think\Controller;

class IndexController extends Controller {

    protected function _initialize(){

    }

    public function index(){
    	
    	json_msg(L('ERROR_DEFAULT'), 1);
    }

}
