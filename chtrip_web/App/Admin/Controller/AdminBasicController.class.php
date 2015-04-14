<?php
/**
 * 后台首页控制器
 *
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\AdminBasic;

class AdminBasicController extends Controller {

    static public $Auth;
    public $adminBasicModel;

    /**
     * 后台控制器初始化
     */
    protected function _initialize(){

        if (!$this->Auth) {
            $this->Auth = new \Think\Auth();
        }

        $name = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;

        if (!$this->Auth->check($name, cookie('uid'))) {
            $this->error(L('error_auth'), U('Login/login'));
        }

        $this->adminBasicModel = D('AdminBasic');

        $this->_assignText();
        // 设置左侧菜单栏
        $this->_assignLeftMenu();

        // 设置用户操作日志
        // $this->_setUserLog();
    }

    /**
     * 定义默认现实文本
     */
    private function _assignText(){
        $this->assign('_adminUser', sprintf(L('admin_user_name'), cookie('admin_user')));
        $this->assign('header_list', $this->adminBasicModel->getHeaderList());
    }

    /**
     * 设置对应权限菜单
     *
     */
    private function _assignLeftMenu(){
        $this->assign('_adminLeftMenu', $this->adminBasicModel->getMenu());
    }

    /**
     * 设置用户操作日志
     * @2014-7-29
     */
    private function _setUserLog(){
        $this->adminBasicModel->setUserLog();
    }
}
