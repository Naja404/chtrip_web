<?php
/**
 * 
 * @author hisoka
 *
 */
namespace Admin\Controller;
use Admin\Controller\AdminBasicController;

/**
 * 后台首页控制器
 */
class IndexController extends AdminBasicController {

    public $indexModel;

    public function _initialize(){
        parent::_initialize();

        $this->indexModel = D('Index');
    }

    /**
     * 后台首页
     */
    public function index(){
        
        $this->assign('title', L('title'));
        
        $this->assign('text_logout', L('text_logout'));

        $this->display();
    }

}
