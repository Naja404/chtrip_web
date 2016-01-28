<?php
/**
 * 
 * @author hisoka
 *
 */
namespace Admin\Controller;
use Admin\Controller\AdminBasicController;
use Admin\Model\Order;

/**
 * 订单
 */
class OrderController extends AdminBasicController {

    public $orderModel;

    public function _initialize(){
        parent::_initialize();

        $this->orderModel = D('Order');
        $this->_assignText();
    }

    /**
     * 订单列表
     */
    public function lists(){

        $total = $this->orderModel->getOrderTotal();

        $page = new \Think\Page($total, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page'  => page($p, C('PAGE_LIMIT')),
                    'order' => 'A.created DESC',
            );

        $list = $this->orderModel->getOrderList($data);

        $this->assign('page_show', $page->showAdmin());

        $this->assign('list', $list);

        $this->display('Order/lists');

    }

    /**
     * 订单详情
     */
    public function detail(){

        $reqData = I('request.');

        $detail = $this->orderModel->getOrderDetail($reqData['oid']);

        if (!$detail) $this->error(L('ERROR_ADD_PRODUCT'));

        $this->assign('detail', $detail);

        $this->display('Order/detail');
    }

    private function _assignText(){
        $this->assign('title', L('title_'.ACTION_NAME));
    }

}
