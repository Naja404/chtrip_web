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
class ProductController extends AdminBasicController {

    public $productModel;

    public $uploadModel;

    public function _initialize(){
        parent::_initialize();
        
        $this->uploadModel   = D('Upload');
        $this->productModel = D('Product');
    }

    /**
     * 添加产品
     *
     */
    public function addProduct(){

        if (IS_POST) {

            $imageId = $this->_getIMGId();

            $pid = $this->productModel->addProduct($imageId);

            if (!$pid) {
                $this->error(L('ERROR_ADD_PRODUCT'));
            }

            $this->success($statu, U('Product/proList'));
        }

        $this->_assignText();

        $this->display('addProduct');

    }

    /**
     * 产品列表
     *
     */
    public function proList(){

        $count = $this->productModel->getProductCount();
        
        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page' => $p.','.C('PAGE_LIMIT'),
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->productModel->getProductList($data));

        $this->_assignText();

        $this->display();
    }

    /**
     * 产品删除
     *
     */
    public function proDel(){
        
    }

    /**
     * kindeditor 上传图片
     *
     */
    public function uploadIMG(){

        $returnRes = $this->uploadModel->uploadFile();
        
        $returnRes = array(
                'error'   => $returnRes['state'],
                'message' => $returnRes['error'],
                'url'     => $returnRes['url'],
            );
        
        $this->ajaxReturn($returnRes);
    }

    /**
     * 获取上传图片image_id
     *
     */
    private function _getIMGId(){
        
        $imgRes = $this->uploadModel->uploadFile();

        if ($imgRes['path']) {
            return $this->productModel->setIMGId($imgRes['path']);
        }

        return false;
    }

    /**
     * 声明变量
     *
     */
    private function _assignText(){

        $this->assign('title', L('TITLE_'.ACTION_NAME).L('TITLE_SUFFIX'));

        if (in_array(ACTION_NAME, array('addProduct'))) {
            $this->assign('shipping_list', $this->productModel->getShipType());
            $this->assign('saler_list', $this->productModel->getSaler());
            $this->assign('tag_list', $this->productModel->getTags());
        }
    }

}
