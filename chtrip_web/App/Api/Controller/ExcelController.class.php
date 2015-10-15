<?php
/**
 * excel数据处理
 */

namespace Api\Controller;
use Think\Controller;

class ExcelController extends Controller {
    
    // 数据模型
    public $excelModel;

    public $snoopy;

    public $fetch;

    public function _initialize(){  
        $this->salerModel = D('Saler');
    }


    /**
     * 读取excel内容
     *
     */
    public function reader(){
        import('Extend.PHPExcel');

        $filePath = './Public/hengbing_jiudian_831.xls';

        $PHPExcel = new\PHPExcel_Reader_Excel2007();

        if(!$PHPExcel->canRead($filePath)){  
            $PHPExcel = new\PHPExcel_Reader_Excel5();  
            if(!$PHPExcel->canRead($filePath)){  
                echo 'no Excel';  
                return ;  
            }  
        } 

        $phpreader = $PHPExcel->load($filePath);

        $currentSheet = $phpreader->getSheet(0);  

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

            // cacheList('new_shop', $rowInfo[$rowIndex]);
        } 

        $type = array(
            '购物' => '1',
            '美食' => '2',
            '酒店' => '3',
            '景点' => '4',
            );

        foreach ($rowInfo as $k => $v) {

            if ($k == 1) continue;

            $val = array_values($v);

            if (empty($val[0])) continue;

            $add = array(
                    'name'        => $val[0],
                    'description' => is_string($val[1]) ? $val[1] : '',
                    'pic_url'     => $val[2],
                    'address'     => is_string($val[3]) ? $val[3] : '',
                    'open_time'   => !empty($val[4]) ? $val[4] : '',
                    'tel'         => !empty($val[5]) ? $val[5] : '',
                    'avg_price'   => !empty($val[6]) ? '￥'.$val[6].'/人' : '',
                    'avg_rating'  => !empty($val[7]) ? $val[7] : '',
                    'tag_name'    => !empty($val[8]) ? $val[8] : '',
                    'category'    => $val[9],
                    'area'        => $val[10],
                    'type'        => $type[$val[11]],
                    'status'      => 1,
                );

            $this->salerModel->add($add);
        }
        
    }

    public function brandMallProduct(){
        $list = $this->brandMallOtherModel->select();
        foreach ($list as $k => $v) {
            $where = array(
                    'tb_brand_id' => $v['tb_brand_id'],
                    'tb_mall_id' => $v['tb_mall_id'],
                );

            $brandMallRes = $this->brandMallModel->where($where)->find();

            if ($brandMallRes['id']) {
                $this->brandMallOtherModel->where(array('id' => $v['id']))->save(array('mark' => 1));
                continue;
            }

            $v['id'] = makeUUID();

            $this->brandMallModel->add($v);

        }
    }



    public function insertShop(){



        while ($i <= 3568) {
            $cache = cacheList('new_shop');
            foreach ($cache as $k => $v) {
                $tmpData[] = $v;
            }

            foreach ($tmpData as $k => $v) {
                $newData[$this->insertShopField[$k]] = $v;
            }

            $this->saojieModel->add($newData);

            unset($tmpData);
            unset($newData);

            $i++;
        }


    }

    /**
     * 读取excel内容
     *
     */
    public function readerComment(){
        import('Extend.PHPExcel');

        $filePath = './Public/mall_comment_0729.xls';

        $PHPExcel = new\PHPExcel_Reader_Excel2007();

        if(!$PHPExcel->canRead($filePath)){  
            $PHPExcel = new\PHPExcel_Reader_Excel5();  
            if(!$PHPExcel->canRead($filePath)){  
                echo 'no Excel';  
                return ;  
            }  
        } 

        $phpreader = $PHPExcel->load($filePath);

        $currentSheet = $phpreader->getSheet(0);  

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

            cacheList('mall_comment', $rowInfo[$rowIndex]);

            unset($rowInfo);
          
        } 
    }

    public function formatComment(){
        $cache = cacheList('mall_comment');
        // $cache = json_decode('{"A25":"\u957f\u98ce\u666f\u7554","B25":"3ffdbc685caea337aa7befbec44c15c0","C25":"\u4eca\u5929\u4e5f\u7206\u6ee1\uff0c\u5403\u7684\u9009\u62e9\u592a\u6709\u9650\u3002","D25":"\u6d77\u5c1a\u4e00\u54c1\u5728\u666e\u9640\u533a\u957f\u98ce\u666f\u7554\u5e7f\u573a\u7684\u4e09\u697c\uff0c\u8bc4\u4ef7\u5f88\u9ad8\u554a\uff0c\u53ef\u4ee5\u53bb\u5403","E25":"\u4e70\u4e86\u5f88\u591a\uff0c\u6298\u6263\u8fd8\u884c","F25":"\u670d\u52a1\u5458\u6001\u5ea6\u4e0d\u9519","G25":"\u4fc3\u9500\u6d3b\u52a8\u633a\u591a\u7684","H25":null}', true);

        foreach ($cache as $k => $v) {
            $newComment[] = $v;
        }

        foreach ($newComment as $k => $v) {
            if ($k == 1) {
                $mallId = $v;
            }

            if ($k > 1) {
                if (!empty($v)) {
                    $comment[] = $v;
                }
            }
        }

        $saveCache = array(
                'mallId' => $mallId,
                'comment' => $comment,
            );

        foreach ($comment as $j => $m) {

            $time = $this->getRandTime();

            $add = array(
                    'id'          => makeUUID(),
                    'type'        => 1,
                    'tb_user_id'  => $this->getRandUserId(),
                    'tb_obj_id'   => $mallId,
                    'content'     => $m,
                    'grade'       => rand(3, 5),
                    'create_time' => $time,
                    'update_time' => $time,

                );

            $this->commentModel->add($add);
        }

    }

    public function addComment(){
        // $cache = json_decode('{"brandZh":null,"brandEn":"\u9c81\u8089\u8303","comment":["\u559c\u6b22\u5364\u8089\u996d\u7684\u6211\u600e\u4e48\u53ef\u80fd\u9519\u8fc7\u5462\u3002","\u80a5\u7626\u76f8\u95f4\uff0c\u7c73\u996d\u8f6f\u8f6f\u7684\u6709\u56bc\u52b2\u3002","\u7b97\u662f\u4e00\u5bb6\u5403\u53f0\u5f0f\u5364\u8089\u996d\u7684\u5feb\u9910\u5e97\u5427\uff0c\u5364\u8089\u996d\u5957\u9910\u611f\u89c9\u6027\u4ef7\u6bd4\u8fd8\u53ef\u4ee5\u3002","\u597d\u5728\u6001\u5ea6\u8fd8\u662f\u4e0d\u9519\u7684","\u5364\u8089\u771f\u7684\u505a\u7684\u5f88\u5165\u5473\uff5e\u914d\u996d\u5f88\u597d\u5403\u2026"]}', true);
        
        $cache = cacheList('comment_arr_bak');
        if (!$cache) {
            return;
        }
        $where = array(
                'name_zh' => empty($cache['brandZh']) ? '' : $cache['brandZh'],
                'name_en' => empty($cache['brandEn']) ? '' : $cache['brandEn'],
            );

        $queryRes = $this->brandModel->where($where)->find();

        if (!$queryRes['id']) {
            cacheList('comment_fail', $cache);
            return;
        }

        foreach ($cache['comment'] as $j => $m) {

            $time = $this->getRandTime();
            $add = array(
                    'id'          => makeUUID(),
                    'type'        => 2,
                    'tb_user_id'  => $this->getRandUserId(),
                    'tb_obj_id'   => $this->getRandMallId($queryRes['id']),
                    'content'     => $m,
                    'grade'       => rand(3, 5),
                    'create_time' => $time,
                    'update_time' => $time,

                );

            $this->commentModel->add($add);
        }

        cacheList('comment_arr_bak_1', $cache);
    }

    public function formatFloor(){
        $list = $this->brandMallModel->group('tb_mall_id')->select();

        foreach ($list as $k => $v) {
            $queryRes = $this->brandMallModel->where(array('tb_mall_id' => $v['tb_mall_id']))->group('address')->select();

            foreach ($queryRes as $j => $m) {
                $floor[] = $m['address'];
            }
            $floor = implode(',', $floor);
            $floor = explode(',', $floor);
            $floor = array_unique($floor);

            foreach ($floor as $a => $b) {
                $add = array(
                        'id'          => makeUUID(),
                        'name_en'     => $b,
                        'name_zh'     => $b,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s'),
                        'tb_mall_id'  => $v['tb_mall_id'],
                    );

                $this->mallFloorModel->add($add);
            }
            unset($floor);
        }
    }

    public function addMallFloor(){
        import('Extend.PHPExcel');

        $filePath = './Public/mall_floor_730.xls';

        $PHPExcel = new\PHPExcel_Reader_Excel2007();

        if(!$PHPExcel->canRead($filePath)){  
            $PHPExcel = new\PHPExcel_Reader_Excel5();  
            if(!$PHPExcel->canRead($filePath)){  
                echo 'no Excel';  
                return ;  
            }  
        } 

        $phpreader = $PHPExcel->load($filePath);

        $currentSheet = $phpreader->getSheet(0);  

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

            foreach ($rowInfo[$rowIndex] as $j => $m) {
                $newMall[] = $m;
            }

            $update = array(
                    'store_num'   => $newMall[5] ? $newMall[5] : '',
                    'update_time' => date('Y-m-d H:i:s'),
                );
            $this->brandMallModel->where(array('id' => $newMall[0]))->save($update);
            // cacheList('mall_comment', $rowInfo[$rowIndex]);
            unset($newMall);
            unset($rowInfo);
          
        } 
    }

    public function getRandUserId(){
        $queryRes = $this->brandModel->query("select * from tb_user order by rand() limit 1");

        return $queryRes[0]['id'];
    }

    public function getRandMallId($brandId = 0){

        $queryRes = $this->brandModel->query("select a.id from tb_brand_mall as a
                                    left join tb_mall as b on b.id = a.tb_mall_id
                                    where a.tb_brand_id = '".$brandId."' 
                                    order by rand() limit 1");

        return $queryRes[0]['id'];
    }

    public function getRandTime(){
        return date('Y-m-d H:i:s',rand(1437321600, 1438012800));
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
     *
     */
    private function _initParam(){

        $this->fetch = new \FetchHTML('Data');

    }

}
