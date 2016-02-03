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

        $this->adModel = D('Ad');

        $this->reqURI = md5($_SERVER['REQUEST_URI']);
    }

    /**
     * 用户协议
     */
    public function userProtocol(){
        $this->display('Product/protocol');
    }

    /**
     * 城市列表
     */
    public function cityList(){

        if (I('request.ver')) {
            $outPut = array(
                    'cityList' => $this->productModel->getAllCityList(),
                    'version'  => '0.9.4',
                );

        }else{
            $cityList = $this->productModel->getCityList();

            $outPut = array(
                    'cityList' => $cityList,
                    'hasNew'   => '0',
                );
        }

        json_msg($outPut);
    }

    /**
     * 搜索canting
     * @param string name
     */
    public function searchResturant(){
        
        $reqData = array(
                'name' => I('request.name', 'tok'),
                'lang' => I('request.lang'),
            );

        $json =  file_get_contents("http://api.gnavi.co.jp/ForeignRestSearchAPI/20150630/?keyid=5aeef30c300c1575ebf4226f6ff336f6&format=json&lang=".$reqData['lang']."&name=".$reqData['name']);
        // $json =  file_get_contents("http://api.gnavi.co.jp/ForeignRestSearchAPI/20150630/?keyid=5aeef30c300c1575ebf4226f6ff336f6&format=json&&name=".$reqData['name']);

        echo '<pre>';
        print_r(json_decode($json, true));exit();
    }

    /**
     * 专辑列表
     * @param int $pageSize 每页数据内容
     * @param int $pageNum 页数
     */
    public function albumList(){

        $pageSize = I('request.pageSize', C('PAGE_LIMIT'));
        $pageNum  = I('request.pageNum', 1);

        $queryData = array(
                'page'  => make_page($pageNum, $pageSize),
                'where' => array('status' => 1),
            );

        $count = $this->productModel->getAlbumCount($queryData);


        $queryRes = $this->productModel->getAlbumList($queryData);

        $queryArr = array();

        $i = 0;

        foreach ($queryRes as $k => $v) {
            $i++;
            $v['path'] = C('API_WEBSITE').$v['path'];
            // if (!empty($v['title_btn'])) {
            //     $v['colorNum'] = (string)($i);
            // }
            $v['title'] = htmlspecialchars_decode($v['title']);
            
            if (I('request.ver') != '0.9.7') $v['title'] = str_replace('*', '', $v['title']);

            $v['colorNum'] = (string)($i);
            $v['activityTime'] = $this->_setActivityTime($v['activityTime']);
            $queryArr[] = $v;

            if ($i == 4) $i = 0;

        }

        $adSql = "select 
                    a.type,
                    a.title,
                    concat('http://api.nijigo.com', b.path) as path,
                    a.url,
                    a.url_id as pid,
                    c.price_zh 
                     from ch_ad as a
                    left join ch_product_image as b on b.gid = a.image_id 
                    left join ch_product_detail_copy as c on c.pid = a.url_id
                    ORDER BY a.sort ASC";
                    
        $adList = $this->adModel->query($adSql);

        $outPut = array(
                'adList'      => $adList,
                'albumList'   => $queryArr,
                'hasMore'     => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? '1' : '0',
                'nextPageNum' => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? (string)++$pageNum : '1',
            );

        json_msg($outPut);


    }
    /**
     * 专辑显示
     * @param int aid 专辑id
     */
    public function showAlbum(){

        $aid = I('request.aid', 0);

        $detailRes = $this->productModel->getAlbumDetail($aid);

        if (preg_match('/{hasPro:\w+}/', $detailRes['content'])) {
            $detailRes['content'] = $this->_formatProToHTML($detailRes['content']);
        }

        $detailRes['title'] = str_replace('*', '', $detailRes['title']);

        $this->assign('detail', $detailRes);

        $this->display('Product/albumDetail');
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
                'where' => $this->_getProListWhere(),
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
            $v['price_zh'] = $v['price_zh'].' RMB';
            $v['stock_label'] = L('TEXT_ADD_CART');
            if ($v['rest'] == 0) $v['stock_label'] = L('TEXT_NOT_STOCK');
            $queryArr[] = $v;
        }

        $outPut = array(
                'proList' => $queryArr,
                'hasMore' => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? '1' : '0',
                'nextPageNum' => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? (string)++$pageNum : '1',
            );

        // cache(C('CACHE_LIST.PRODUCT_LIST').$reqURI, $outPut, 3600);

        json_msg($outPut);
    }

    /**
     * 显示产品详细页介绍
     * @param int $pid 产品id
     */
    public function showProDetail(){

        $pid = I('request.pid', false);

        $queryRes = $this->productModel->getProductDetail($pid);

        if (is_array($queryRes) && count($queryRes)) {
            $queryRes['description_zh'] = htmlspecialchars_decode($queryRes['description_zh']);
            $queryRes['description_jp'] = htmlspecialchars_decode($queryRes['description_jp']);
            $queryRes['tag_name']    = explode(',', $queryRes['tag_name']);
        }

        $this->assign('detail', $queryRes);
        $this->display('Product/detail');

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

    /**
     * 商家列表
     */
    public function shopList(){
        $pageSize = I('request.pageSize', C('PAGE_LIMIT'));
        $pageNum  = I('request.pageNum', 1);
        $shopType = I('request.shopType', 1);
        $cityName = I('request.cityName', 'all');
        $category = I('request.category', 'all');
        $sort = I('request.sort', '0');

        $queryData = array(
                'page'  => make_page($pageNum, $pageSize),
                'where' => array('type' => (int)$shopType, 'status' => 1),
                'order' => '',
            );

        if (!empty($cityName) && $cityName != 'all') $queryData['where']['area'] = array('LIKE', "%".$cityName."%");

        if (!empty($category) && $category != 'all') $queryData['where']['category'] = array('LIKE', "%".$category."%");

        if (!empty($sort) && $sort != '0') $queryData['where']['avg_rating'] = $this->_getAvgRating($sort);

        $count = $this->productModel->getShopCount($queryData);
        $queryRes = $this->productModel->getShopList($queryData);

        $queryArr = array();

        foreach ($queryRes as $k => $v) {
            $v['avg_rating'] = (string)10*$v['avg_rating'];
            $v['googlemap'] = sprintf("comgooglemaps://?q=%s&center=%s,%s&views=traffic&zoom=15
", urlencode($v['address']), $v['lat'], $v['lng']);
            $queryArr[] = $v;
        }

        $outPut = array(
                'shopList'    => $queryArr,
                'hasMore'     => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? '1' : '0',
                'nextPageNum' => ($count - make_page($pageNum, $pageSize, 1)) > 0 ? (string)++$pageNum : '1',
            );

        json_msg($outPut);

    }

    /**
     * 商户详情
     * @param int $sid 商户id
     */
    public function showShopDetail(){
        $sid = I('request.sid', false);

        $queryRes = $this->productModel->getShopDetail($sid);

        if (is_array($queryRes) && count($queryRes)) {
            $queryRes['tag_name']    = explode(',', $queryRes['tag_name']);
            if (empty($queryRes['tag_name'][0])) {
                $queryRes['tag_name'] = array();
            }
        }
        
        if (empty($queryRes['address_img'])) $queryRes['address_img'] = $this->_getMapImg($queryRes);

        $this->assign('detail', $queryRes);
        $this->display('Product/shopDetail');
    }

    /**
     * 关于我们
     */
    public function aboutme(){
        $this->display('Product/aboutme');
    }

    /**
     * 获取品牌列表查询条件
     */
    private function _getProListWhere(){
        $brand    = I('request.brand');
        $cate     = I('request.cate');
        $sort     = I('request.sort');
        $priceRange = array(
            "200以下"     => "<= 200", 
            "200-500"    => " BETWEEN 200 AND 500 ", 
            "1000-2000" => " BETWEEN 1000 AND 2000 ", 
            "2000-5000"  => " BETWEEN 2000 AND 5000 ", 
            "5000-10000" => " BETWEEN 5000 AND 10000 ",
            "1万以上"     => " >= 10000",
            );

        if (!empty($cate) && !in_array($cate, array('类别'))) $where[] = " AND b.category = '".$cate."'";

        if (!empty($brand) && !in_array($brand, array('品牌'))) $where[] = " AND b.brand LIKE '%".$brand."%'";

        if (!empty($sort) && in_array($sort, array_keys($priceRange)) ) $where[] = " AND b.price_zh ".$priceRange[$sort];

        if (count($where) <= 0) return NULL;

        $whereStr = implode('', $where);

        return $whereStr;
    }

    /**
     * 获取星级数字
     * @param string 星级中文
     */
    private function _getAvgRating($rating = '五星'){
        $arr = array(
                '一星' => 1,
                '二星' => 2,
                '三星' => 3,
                '四星' => 4,
                '五星' => 5,
            );

        return $arr[$rating];
    }

    /**
     * 计算剩余时间
     * @param int $activityTime 活动剩余时间差
     */
    private function _setActivityTime($activityTime = 0){

        if ($activityTime <= 0) {
            return '0';
        }

        if ($activityTime < 3600*24) {
            return sprintf('剩:%s小时', ceil($activityTime / 3600));
        }else{
            return sprintf('剩:%s天', ceil($activityTime / 3600 / 24));
        }
    }

     /**
      * 格式化产品内容
      * @param string $content 文本内容
      */
     private function _formatProToHTML($content = false){
        preg_match_all('/{hasPro:\w+}/', $content, $pregArr);

        if (count($pregArr[0]) <= 0) return $content;

        foreach ($pregArr[0] as $k => $v) {
            $tmpHtml = $this->_checkProType($v);
            $content = str_replace($v, $tmpHtml, $content);
        }

        return $content;
     }

     /**
      * 返回类型
      * @param string $pidStr
      */
    private function _checkProType($pidStr = false){
        preg_match('/\d+/', $pidStr, $pid);

        if (preg_match('/{hasPro:pid_\\d+}/', $pidStr)) {
            $queryRes = $this->productModel->getProductDetail($pid[0]);
            $htmlFile = 'Product/proTpl';
        }else{
            $queryRes = $this->productModel->getShopDetail($pid[0]);

            if (empty($queryRes['address_img'])) $queryRes['address_img'] = $this->_getMapImg($queryRes);


            $htmlFile = 'Product/shopTpl';
        }

        $this->assign('ssid', I('request.ssid'));
        $this->assign('data', $queryRes);

        return $this->fetch($htmlFile);
    }

     /**
      * 获取地图图片
      * @param array $addRes 查询内容
      */
    private function _getMapImg($addRes = array()){
        $latlng = google_geo($addRes['address']);

        if (!is_array($latlng)) return '1';

        $conf = C('GOOGLE_CONF.STATIC_IMAGE_CONF');

        $conf['latlng'] = implode(',', array_values($latlng));

        $filePath = google_static_image($addRes['address'], $conf);

        if ($filePath == false) return '2';

        $update = array(
                'lat'         => $latlng['lat'],
                'lng'         => $latlng['lng'],
                'address_img' => $filePath,
            );

        $this->productModel->upSalerInfo($addRes['saler_id'], $update); 

        return $filePath;
    }
}
