<?php
/**
 * api util 使用工具模块
 * @author Hisoka <hisoka.2pac@gmail.com>
 */
namespace Api\Controller;
use Think\Controller;

class UtilController extends ApiBasicController {

	/**
	 * 工具模块
	 */
	public $utilModel;

    protected function _initialize(){
        $this->utilModel = D('util');
    }

    /**
     * 设置token内容
     *
     */
    public function setToken(){

    	if (!I('request.token')) {
    		json_msg(L('ERROR_PARAM'), 1);
    	}
        
        $status = $this->utilModel->setToken(I('request.token'));

        if (!$status) {
            json_msg(L('ERROR_SET_TOKEN'), 1);
        }

        $data = array(
                'ssid' => $this->utilModel->getSSID($status, I('request.token')),
            );

        json_msg($data);

    }

    /**
     * 发送用户反馈
     *
     */
    public function feedback(){
        $content = I('get.content', 'htmlspecialchars');
        $content .= '<br/>DeviceToken:'.I('get.token');

        $to = array(
                '326101710@qq.com',
            );
        $subject = 'NijiGo FeedBack';

        send_mail($to, $subject, $content);

        json_msg();
    }

}
