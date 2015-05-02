<?php
/**
 * 
 * @author hisoka
 *
 */
namespace Admin\Controller;
use Admin\Controller\AdminBasicController;
use Admin\Model\Setting;

/**
 * 后台设置控制器
 */
class SettingController extends AdminBasicController {

    public $settingModel;

    public function _initialize(){
        parent::_initialize();

        $this->settingModel = D('Setting');
        $this->_assignText();
    }

    /**
     * 后台菜单列表
     *
     */
    public function menuList(){
        $count = $this->settingModel->table(tname('admin_auth_rule'))->where('status != 0')->count();

        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'where' => 'status != 0',
                    'page'  => $p.','.C('PAGE_LIMIT'),
                    'order' => 'menu_module, id ASC',
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->settingModel->getMenus($data));
        $this->display('menu-lists');
    }

    /**
     * 后台菜单添加/修改
     *
     */
    public function setMenuForm(){
        if (!IS_AJAX) {
            $this->ajaxReturn(array('status' => 1, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->setMenuForm(I('post.'));

        // 清空redis中缓存内容
        // rm_all_cache('bjy_Admin:*');

        $this->ajaxReturn($result);
    }

    /**
     * 获取菜单详细内容
     *
     */
    public function getMenuDetail(){
        $rid = I('post.rid');

        if (!$rid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->getMenuDetail(intval($rid));

        $this->ajaxReturn($result);
    }

    /**
     * 根据id删除菜单
     *
     */
    public function delMenu(){
        $rid = I('post.rid');

        if (!$rid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->delMenu(intval($rid));

        // 清空redis中缓存内容
        // rm_all_cache('bjy_Admin:*');

        $this->ajaxReturn($result);
    }

    /**
     * 获取用户列表
     *
     */
    public function userList(){
        $count = $this->settingModel->table(tname('admin_user'))->count();

        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page'  => (($p - 1) * C('PAGE_LIMIT')).','.C('PAGE_LIMIT'),
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->settingModel->getUsers($data));
        $this->assign('group_list', $this->settingModel->getGroup());
        $this->display('user-lists');
    }

    /**
     * 用户添加/修改
     *
     */
    public function setUserForm(){
        if (!IS_AJAX) {
            $this->ajaxReturn(array('status' => 1, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->setUserForm(I('post.'));

        $this->ajaxReturn($result);
    }

    /**
     * 根据uid获取用户详细内容
     *
     */
    public function getUserDetail(){
        $uid = I('post.uid');

        if (!$uid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->getUserDetail(intval($uid));

        $this->ajaxReturn($result);
    }

    /**
     * 根据uid删除用户
     *
     */
    public function delUser(){
        $uid = I('post.uid');

        if (!$uid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->delUser(intval($uid));
        
        $this->ajaxReturn($result);
    }

    /**
     * 声明变量
     *
     */
    private function _assignText(){
        $this->assign('title', L('title_'.ACTION_NAME));
    }

    /**
     * 用户组管理列表
     *
     */
    public function groupList(){
        $count = $this->settingModel->table(tname('admin_auth_group'))->count();

        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page'  => $p.','.C('PAGE_LIMIT'),
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->settingModel->getGroups($data));
        $this->assign('rules', $this->_getRules());

        $this->display('group-lists');
    }

    /**
     * 获取所有权限内容
     *
     */
    private function _getRules(){
        // if (cache('bjy_Admin:Rules:list')) {
        //     return unserialize(cache('bjy_Admin:Rules:list'));
        // }

        $result = $this->settingModel->getMenus();

        $menuArr = array();
        
        foreach ($result as $k => $v) {

            if (!in_array($v['menu_module'], array_keys($menuArr))) {
                $menuArr[$v['menu_module']] = array(
                        'title' => $v['menu_title'],
                        'module' => $v['menu_module'],
                    );
            }

            $menuArr[$v['menu_module']]['list'][] = array(
                        'rid' => $v['id'],
                        'title' => $v['title'],
                );
        }

        if (count($menuArr)) {
            // cache('bjy_Admin:Rules:list', serialize($menuArr), 3600);
        }

        return $menuArr;
    }

    /**
     * 设置用户组 添加 / 修改 
     *
     */
    public function setGroupForm(){

        if (!IS_AJAX) {
            $this->ajaxReturn(array('status' => 1, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->setGroupForm(I('post.'));

        // rm_all_cache('bjy_Admin:*');

        $this->ajaxReturn($result);
    }

    /**
     * 获取用户组详细内容
     *
     */
    public function getGroupDetail(){
        $rid = I('post.rid');

        if (!$rid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->getGroupDetail(intval($rid));

        $this->ajaxReturn($result);
    }

    /**
     * 根据id删除用户组
     *
     */
    public function delGroup(){
        $gid = I('post.gid');

        if (!$gid) {
            $this->ajaxReturn(array('status' => 0, 'msg' => L('error_param')));
        }

        $result = $this->settingModel->delGroup(intval($gid));

        $this->ajaxReturn($result);
    }

}
