<?php
/**
 * excel数据处理
 */

namespace Admin\Controller;
use Admin\Controller\AdminBasicController;
use Think\Upload;
use Think\Model;

class ExcelController extends AdminBasicController {
    
    // 数据模型
    public $excelModel;

    public $snoopy;

    public $fetch;

    public $upload;

    public function _initialize(){  
        parent::_initialize();

        $this->excelModel = D('Excel');
    }

    /**
     * 爬虫功能
     */
    public function fetchBug(){

        if (IS_AJAX) {
            $this->_initSnoopy();

            $newStatus = $this->_initParam(I('request.url'));
            
            $reqUrl = I('request.url');
            
            switch ($newStatus) {
                case 'XiaoTaoJiang':
                    // http://app.xiaotaojiang.com/ui/products/sDcyVQy
                    // http://app.xiaotaojiang.com/ws/products/sDcyVQy?collect=1
                    
                    $productId = str_replace('http://app.xiaotaojiang.com/ui/products/', '', $reqUrl);

                    $reqUrl = sprintf("http://app.xiaotaojiang.com/ws/products/%s?collect=1", $productId);

                    break;
                case 'Bolome':
                    //https://m.bolome.com/#/product/143617597562435
                    // https://a.bolo.me/v2/catalogs/143617597562435

                    $this->snoopy->rawheaders['tourId'] = "EBADDCEC-D689-49D9-89E7-9865DA2E7200";

                    $productId = str_replace('https://m.bolome.com/#/product/', '', $reqUrl);

                    $reqUrl = sprintf("https://a.bolo.me/v2/catalogs/%s", $productId);

                    break;
                default:
                    $reqUrl = I('request.url');
                    break;
            }

            $htmlRes = $this->snoopy->fetch($reqUrl);

            if ($newStatus === false) {
                $ajaxRes = array(
                        'status' => '1',
                        'msg'    => '暂无该URL地址数据',
                    );
            }else{
                $returnRes = $this->fetch->fetch($htmlRes->results, 'getShop');

                $hasTitle = $newStatus == 'Enjoytokyo' ? true : false;

                $ajaxRes = array(
                        'status' => '0',
                        'html'   => $this->_setFetchHtml($returnRes, $hasTitle),
                    );
            }

            $this->ajaxReturn($ajaxRes);
        }

        $this->assign('title', '爬虫功能');
        $this->display('Excel/fetchBug');

    }

    /**
     * 导入产品/店铺数据
     */
    public function insertPro(){

        if (IS_POST) {

            $fileInfo = $this->_initUpload();

            $excelConf = array(
                    'sheet'    => I('request.type') == 'product' ? 1 : 0,
                    'filePath' => $fileInfo['realpath'],
                );
            
            $excelData = $this->getExcelInfo($excelConf);

            if (I('request.type') == 'product') {
                return $this->insertProData($excelData, true);
            }else{
                return $this->insertShopData($excelData, true);
            }
        }

        $this->assign('title', '导入数据');
        $this->display('Excel/insertPro');
    }

    /**
     * 显示导入产品列表数据
     * @param array 产品数据
     * @param bool 是否类中调用
     */
    public function insertProData($data = array(), $hasClass = false){

        if (IS_POST && !$hasClass) {
            $data = json_decode(file_get_contents(I('request.dataPath')), true);

            $addRes = $this->excelModel->insertProData($data);

            if (!$addRes) $this->error('商品导入失败');

            @unlink(I('request.dataPath'));

            $this->success($statu, U('Product/proList'));
            exit;
        }

        $proData = $this->_formatExcelData($data);
        
        $dataPath = writeFile(json_encode($proData['data']));

        $this->assign('title', '商品数据列表');
        $this->assign('listTitle', $proData['title']);
        $this->assign('list', $proData['data']);
        $this->assign('dataPath', $dataPath);
        

        $this->display('Excel/productList');
    }

    /**
     * 显示导入商家列表数据
     * @param array 商家数据
     * @param bool 是否类中调用
     */
    public function insertShopData($data = array(), $hasClass = false){

        if (IS_POST && !$hasClass) {
            $data = json_decode(file_get_contents(I('request.dataPath')), true);

            $addRes = $this->excelModel->insertShopData($data);

            if (!$addRes) $this->error('商家导入失败');

            @unlink(I('request.dataPath'));

            $this->success($statu, U('Shop/shopList'));
            exit;
        }

        $shopData = $this->_formatExcelData($data, 'shop');

        $dataPath = writeFile(json_encode($shopData['data']));

        $this->assign('title', '商家数据列表');
        $this->assign('listTitle', $shopData['title']);
        $this->assign('list', $shopData['data']);
        $this->assign('dataPath', $dataPath);

        $this->display('Excel/shopList');   
    }

    /**
     * 格式化数据
     * @param array $data 商品数据
     */
    private function _formatExcelData($data = array(), $formatType = 'product'){

        if ($formatType == 'product') {
            $arrKey = C('EXCEL_INSERT_PRODUCT_ARR');
        }else{
            $arrKey = C('EXCEL_INSERT_SHOP_ARR');
        }
        

        foreach ($data as $k => $v) {

            if ($k == 1) {
                $title = array_filter(array_values($v));
                continue;
            }

            $tmpV = array_values($v);
            $tmpArr = array();

            if (empty($tmpV[1])) continue;

            foreach ($tmpV as $j => $m) {
                
                if (!$arrKey[$j]) continue;

                $tmpArr[$arrKey[$j]] = empty($m) ? '' : $m;
            }

            $newData[] = $tmpArr;
        }

        $DataRes = array(
                'title' => $title,
                'data'  => $newData,
            );

        return $DataRes;
    }

    /**
     * 格式化商家数据
     * @param array $data 商家数据
     */
    private function _formatExcelDataToShop($data = array()){
        return $data;
    }

    /**
     * 初始化上传模块  
     */
    public function _initUpload(){
        
        $config = C('EXCEL_INSERT_CONF');

        $this->upload = new \Think\Upload($config);// 实例化上传类

        // 上传文件 
        $info   =   array_values($this->upload->upload());

        if(!$info) {// 上传错误提示错误信息
            $this->error($this->upload->getError());
        }

        $info = $info[0];

        $info['realpath'] = './'.$config['rootPath'].$info['savepath'].$info['savename'];

        return $info;
    }

    /**
     * 读取excel内容
     * @param array $Conf excel配置信息
     */
    public function getExcelInfo($Conf = array()){
        import('Extend.PHPExcel');

        $filePath = $Conf['filePath'];

        $PHPExcel = new\PHPExcel_Reader_Excel2007();

        if(!$PHPExcel->canRead($filePath)){  
            $PHPExcel = new\PHPExcel_Reader_Excel5();  
            if(!$PHPExcel->canRead($filePath)){  
                echo 'no Excel';  
                return ;  
            }  
        } 

        $phpreader = $PHPExcel->load($filePath);

        $currentSheet = $phpreader->getSheet($Conf['sheet']);  

        /**取得最大的列号*/  
        $allColumn_s = $currentSheet->getHighestColumn();  

        /**取得一共有多少行*/  
        $allRow = $currentSheet->getHighestRow(); 


        for($rowIndex=1;$rowIndex <= $allRow;$rowIndex++){  

            $colIndex = ord('A');
            $allColumn = ord($allColumn_s);

            for($colIndex; $colIndex<=$allColumn;$colIndex++){  
                $addr = chr($colIndex).$rowIndex;  

                $cell = $currentSheet->getCell($addr)->getValue();  
                if($cell instanceof PHPExcel_RichText)
                    $cell = $cell->__toString();  
                      
                $rowInfo[$rowIndex][$addr] = $cell; 
            }
        }    

        return $rowInfo;
    }

    /**
     * 实例化snoopy
     *
     */
    private function _initSnoopy(){
        import('Extend.Snoopy');
        $this->snoopy = new \Snoopy();
        $this->snoopy->agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';
    }

    /**
     * 初始化参数及配置信息
     * @param string $Domain 域名
     */
    private function _initParam($Domain = 'Enjoytokyo'){

        $Domain = get_domain($Domain);

        $className = C('FETCH_CLASS.'.$Domain);

        if (empty($className)) return false;

        import('Extend.FetchHTML');

        $this->fetch = new \FetchHTML($className);

        return $className;
    }

    /**
     * 设置输出模板
     * @param array $data 数据内容
     * @param bool $hasTitle 
     */
    private function _setFetchHtml($data = array(), $hasTitle = true){
        
        if ($hasTitle) {
            $this->assign('title', $data['title']);
            $this->assign('data', $data['data']);
        }else{
            $this->assign('data', $data);
        }
        
        return $this->fetch('Excel/fetchHtml');
    }
}
