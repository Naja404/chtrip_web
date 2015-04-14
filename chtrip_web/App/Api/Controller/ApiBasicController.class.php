<?php
/**
 * api 基础
 *
 */
namespace Api\Controller;
use Think\Controller;

class ApiBasicController extends Controller {

	// 设备唯一标识
	public $uuid;

	// header信息
	public $header;

	// 用户id
	public $userId;

	// api model
	public $apiBaseModel;

    protected function _initialize(){

    	$this->apiBaseModel = D('ApiBase');

    	$this->_setUserInfo();

    	if (!$this->userId) {
    		json_msg('ERROR_REQUEST_ERROR #10101', 1);
    	}
    	
    }

    /**
     * 设置用户相关信息
     *
     */
    private function _setUserInfo(){
		
		$this->header = get_header_info();

		$this->userId = $this->header['userId'] ? $this->header['userId'] : '0';

		

    	if (!$this->header['UUID']) {
    		$this->uuid = md5(NOW_TIME.rand(1000, 9999));
    	}else{
    		$this->uuid = $this->header['UUID'];
    	}

		if (!cache('bjy_User:'.$this->uuid)) {
			$this->userId = $this->_setUserId();
		}else{
			$this->userId = cache('bjy_User:'.$this->uuid);
		}
		
    }

    /**
     * 设置userid
     *
     */
    private function _setUserId(){

    	$queryRes = $this->apiBaseModel->hasUserId($this->uuid);

    	if (!$queryRes) {
    		$queryRes = md5($this->uuid);
    		$this->apiBaseModel->creratUser($queryRes, $this->uuid);
    	}

    	if ($queryRes) {
    		cache('bjy_User:'.$this->uuid, $queryRes);
    	}

    	return $queryRes;

    }
}
