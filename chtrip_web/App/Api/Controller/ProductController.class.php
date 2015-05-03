<?php
/**
 * api Product 产品模块
 */
namespace Api\Controller;
use Think\Controller;

class ProductController extends ApiBasicController {

    /**
     * product model
     */
    public $productModel;

    /**
     * 请求地址的UIR
     */
    public $reqURI;

    protected function _initialize(){

        // parent::_initialize();

        $this->productModel = D('Product');

        $this->reqURI = md5($_SERVER['REQUEST_URI']);
    }

    /**
     * 获取产品列表
     * @param string tag 标签
     * @param int $pageSize 每页数据内容
     * @param int $pageNum 页数
     */
    public function proList(){

        $tag      = explode(',', I('request.tag'));
        $pageSize = I('request.pageSize', C('PAGE_LIMIT'));
        $pageNum  = I('request.pageNum', 1);

        $queryData = array(
                'page' => make_page($pageNum, $pageSize),
                'tag' => $tag,
                'order' => '',
            );

        $count = $this->productModel->getProductCount($queryData);

        // if ($count == cache(C('CACHE_LIST.PRODUCT_COUNT')) && cache(C('CACHE_LIST.PRODUCT_LIST').$this->reqURI)) {
        //     $outPut = cache(C('CACHE_LIST.PRODUCT_LIST').$this->reqURI);
        //     json_msg($outPut);
        // }

        // cache(C('CACHE_LIST.PRODUCT_COUNT'), $count);

        $queryRes = $this->productModel->getProductList($queryData);

        $queryArr = array();
        foreach ($queryRes as $k => $v) {
            $v['tag_name'] = explode(',', $v['tag_name']);

            $queryArr[] = $v;
        }

        $outPut = array(
                'proList' => $queryArr,
                'hasMore' => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? '1' : '0',
            );

        // cache(C('CACHE_LIST.PRODUCT_LIST').$reqURI, $outPut, 3600);

        json_msg($outPut);
    }

    /**
     * 产品详细介绍
     * @param int $pid 产品id
     */
    public function proDetail(){

        $pid = I('request.pid', false);

        if (!$pid) {
            json_msg(L('ERROR_PARAM'), 1);
        }

        // if (cache(C('CACHE_LIST.PRODUCT_DETAIL').$this->reqURI)) {
        //     $outPut = cache(C('CACHE_LIST.PRODUCT_DETAIL').$this->reqURI);
        //     json_msg($outPut);
        // }

        $queryRes = $this->productModel->getProductDetail($pid);

        if (is_array($queryRes) && count($queryRes)) {
            $queryRes['description_zh'] = htmlspecialchars_decode($queryRes['description_zh']);
            $queryRes['description_jp'] = htmlspecialchars_decode($queryRes['description_jp']);
            $queryRes['tag_name']    = explode(',', $queryRes['tag_name']);
        }

        $outPut = array(
                'proDetail' => $queryRes,
            );

        // cache(C('CACHE_LIST.PRODUCT_DETAIL').$this->reqURI, $outPut, 3600);

        json_msg($outPut);
    }


    /**
     * 产品相关图片
     * @param int $gid 图片父级id
     */
    public function proIMG(){

        $gid = I('request.gid');

        // if (cache(C('CACHE_LIST.PRODUCT_IMG').$this->reqURI)) {
        //     $outPut = cache(C('CACHE_LIST.PRODUCT_IMG').$this->reqURI);
        //     json_msg($outPut);
        // }

        $queryRes = $this->productModel->getIMGByGid($gid);

        $queryArr = array();

        foreach ($queryRes as $k => $v) {
            if ($v['path']) {
                $queryArr[] = $v['path'];
            }
        }

        $outPut = array(
                'imgList' => $queryArr,
            );

        if (count($queryArr) > 0) {
            // cache(C('CACHE_LIST.PRODUCT_IMG').$this->reqURI, $outPut, 3600);
        }

        json_msg($outPut);
    }
}
