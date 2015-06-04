<?php
/**
 * api User 用户模块
 */
namespace Api\Controller;
use Think\Controller;

class UserController extends ApiBasicController {

    /**
     * user model
     */
    public $userModel;

    protected function _initialize(){

        // parent::_initialize();

        $this->userModel = D('User');

    }

    /**
     * 获取扫货清单
     */
    public function getBuyList(){

        $returnRes = $this->userModel->getBuyList(I('request.ssid'));

        if (count($returnRes) > 0) {
            json_msg($returnRes);
        }

        json_msg();
    }



    /**
     * 添加产品到扫货清单
     */
    public function addBuyList(){

        $reqData = array(
                'ssid' => I('request.ssid'),
                'pid' => I('request.pid'),
            );

        $returnRes = $this->userModel->addBuyList($reqData);

        if (is_string($returnRes)) {
            json_msg($returnRes, 1);
        }

        json_msg();
    }

}
