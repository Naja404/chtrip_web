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

    	if (!I('request.token') || !I('request.type')) {
    		json_msg(L('ERROR_PARAM'), 1);
    	}

    	$setData = array(
					'token'      => I('request.token'),
					'type'       => I('request.type'),
					'version'    => I('request.version'),
					'user_agent' => I('request.user_agent'),
					'created'	 => NOW_TIME,
    		);

    	$cacheName = md5(implode('', $setData));

    	// $this->utilModel->setToken($setData);

    	if (cache(C('CACHE_LIST.UTIL_TOKEN').$cacheName)) {
    		json_msg();
    	}

    	cache(C('CACHE_LIST.UTIL_TOKEN').$cacheName, $setData);

    	json_msg();
    }

    /**
     * 发送用户反馈
     *
     */
    public function feedback(){
        $content = I('get.content', 'htmlspecialchars');

        $to = array(
                '326101710@qq.com',
            );
        $subject = 'NijiGo FeedBack';

        send_mail($to, $subject, $content);

        json_msg();
    }

}
