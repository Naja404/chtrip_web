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

    public $ajaxRes;

    public function _initialize(){
        parent::_initialize();
        
        $this->uploadModel   = D('Upload');
        $this->productModel = D('Product');

        $this->ajaxRes = array(
                'status' => '1',
                'msg'    => L('error_operation'),
            );
    }

    /**
     * ajax 查询内容
     */
    public function searchProAjax(){

        $this->ajaxRes['msg'] = '暂无数据';

        if (!IS_AJAX) $this->ajaxReturn($this->ajaxRes);

        if (!I('request.name')) $this->ajaxReturn($this->ajaxRes);

        $queryRes = $this->productModel->searchProAjax(I('request.type'), I('request.name'), true);

        if ($queryRes !== false){
            $this->ajaxRes = array(
                    'status' => '0',
                    'html'   => $queryRes,
                );
        }

        $this->ajaxReturn($this->ajaxRes);
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
     * 编辑产品
     */
    public function editPro(){

        $reqData = I('request.');

        if (IS_POST) {
            $imageId = $this->_getIMGId();

            $pid = $this->productModel->editProduct($imageId);

            if (!$pid) {
                $this->error(L('ERROR_EDIT_PRODUCT'));
            }

            $this->success($statu, U('Product/proList'));
        }

        $detail = $this->productModel->getProductDetail($reqData['pid']);

        $this->assign('detail', $detail);
        $this->_assignText();
        $this->display('editPro');
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

        $this->display();
    }

    /**
     * 删除产品
     * @param int $pid 产品id
     */
    public function delPro(){
        
        if (!IS_AJAX) $this->ajaxReturn($this->ajaxRes);

        $pid = I('request.pid');

        $status = $this->productModel->delPro($pid);

        if ($status === true) $this->ajaxRes = array('status' => '0');

        $this->ajaxReturn($this->ajaxRes);

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

        $this->assign('title', L('TITLE_'.ACTION_NAME));

        if (in_array(ACTION_NAME, array('addProduct', 'editPro'))) {
            $this->assign('brand', $this->productModel->getProBrandCate(1));
            $this->assign('category', $this->productModel->getProBrandCate(2));
        }
    }

}
