<?php
/**
 * 
 * @author hisoka
 *
 */
namespace Admin\Controller;
use Admin\Controller\AdminBasicController;
use Admin\Model\Product;

/**
 * 后台产品控制器
 */
class ShopController extends AdminBasicController {

    public $shopModel;

    public $uploadModel;

    public function _initialize(){
        parent::_initialize();

        $this->shopModel = D('Shop');
        $this->uploadModel   = D('Upload');

    }

    /**
     * 店铺列表
     */
    public function shopList(){
        $count = $this->shopModel->getShopCount();
        
        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page' => $p.','.C('PAGE_LIMIT'),
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->shopModel->getShopList($data));

        $this->display();
    }

    /**
     * 删除商家
     * @param int $id 商家id
     */
    public function delShop(){
        $id = I('get.id');

        $this->shopModel->delShop($id);

        $this->redirect('Shop/shopList');
    }

    /**
     * 添加商家
     */
    public function addShop(){
        
        if (IS_POST) {

            $imageId = $this->_getIMGPath();

            $sid = $this->shopModel->addShop($imageId);

            if (!$sid) {
                $this->error(L('ERROR_ADD_PRODUCT'));
            }

            $this->success($statu, U('Shop/shopList'));
        }

        $this->display();
    }

    /**
     * 获取上传图片路径
     *
     */
    private function _getIMGPath(){
        
        $imgRes = $this->uploadModel->uploadFile();

        if ($imgRes['path']) return $imgRes['path'];

        return false;
    }

}
