<?php
/**
 * 
 * @author hisoka
 *
 */
namespace Admin\Controller;
use Admin\Controller\AdminBasicController;
use Admin\Model\AdminUser;
/**
 * 后台首页控制器
 */
class LoginController extends AdminBasicController {

	static public $loginModel;

	public function _initialize(){
		$this->loginModel = D('AdminUser');
	}

    /**
     * 后台用户登录
     * 
     */
    public function login(){

    	$result = false;
    	$this->assign('title', L('login_title'));

    	if (cookie('uid')) {
    		$this->redirect('Index/index');
    	}

    	if (IS_POST) {

    		$data = array(
		    		'username' => I('post.username'),
		    		'password' => I('post.password'),
    			);

			$result = $this->loginModel->checkLogin($data);

			if ($result === true) {
				$this->redirect('Index/index');
			}
    	}

    	$this->assign('login_error', $result);

        $this->display();
    }

    /**
     * 后台用户登出
     *
     */
    public function logout(){
        cookie(null);
        $this->error(L('text_logout_success'), U('Login/login'));
    }

}
