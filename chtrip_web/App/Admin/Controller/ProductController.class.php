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
        $this->cateModel = D('ProductBrand');

        $this->ajaxRes = array(
                'status' => '1',
                'msg'    => L('error_operation'),
            );
    }

    /**
     * 品牌、分类列表
     * @param type item
     */
    public function cateList(){
        $count = $this->cateModel->getCateTotal();
        
        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page' => $p.','.C('PAGE_LIMIT'),
            );
        $this->_assignText();
        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->cateModel->getCateList($data));

        $this->display();
    }

    /**
     * 添加品牌、分类
     */
    public function addCate(){

        if (IS_AJAX) {
            
            $reqData = I('request.');

            $reqData['app_display'] = isset($reqData['app_display']) && $reqData['app_display'] ? 1 : 0;

            $checkRes = $this->cateModel->checkCate($reqData, 0);

            if (!$checkRes) {
                json_msg('品牌、分类名已存在', 1);
                exit;
            }

            $this->cateModel->add($reqData);

            json_msg();
            exit;
        }
        $this->_assignText();
        $this->display();
    }

    /**
     * 编辑品牌、分类
     */
    public function editCate(){
        
        $reqData = I('request.');

        if (IS_AJAX) {

            $reqData['app_display'] = isset($reqData['app_display']) && $reqData['app_display'] == 'on' ? 1 : 0;

            $checkRes = $this->cateModel->checkCate($reqData, 1);

            if (!$checkRes) {
                json_msg('品牌、分类名已存在', 1);
                exit;
            }

            $where = array(
                    'id' => $reqData['id']
                );

            $this->cateModel->where($where)->save($reqData);

            json_msg();
            exit;
        }
        $this->_assignText();
        $this->assign('detail', $this->cateModel->getCateById($reqData['id']));
        $this->display();
    }

    /**
     * 删除品牌、分类
     */
    public function delCate(){
        
        if (!IS_AJAX) return false;

        $where = array(
                'id' => I('request.id'),
            );

        $this->cateModel->where($where)->delete();

        json_msg();
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

            $imageId = $this->_saveImages(I('request.imagePath'));

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
            
            $imageId = $this->_saveImages(I('request.imagePath'));

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

        $this->_assignText();

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
     * 上传图片
     */
    public function uploadImage(){

        $ajaxRes = array(
                'state' => 1,
                'msg'   => L('ERROR_PARAM'),
            );

        if (!IS_POST) {
            echo json_encode($ajaxRes);exit;
        }

        $reqData = I('request.');

        $imgRes = $this->uploadModel->uploadFile();

        if (!$imgRes['path']) {
            $ajaxRes['msg'] = L('ERR_UPLOAD');
        }else{
            $ajaxRes = array(
                    'state' => 0,
                    'thumb' => C('API_WEBSITE').$imgRes['path'],
                    'path'  => $imgRes['path'],
                );
        }

        echo json_encode($ajaxRes);exit;
    }

    /**
     * 多张图片上传
     * @param array $images 图片路径数组
     */
    private function _saveImages($images = array()){
        return $this->productModel->setImagesId($images);
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
