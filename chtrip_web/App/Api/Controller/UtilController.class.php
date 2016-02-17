<?php
/**
 * api util 使用工具模块
 * @author Hisoka <hisoka.2pac@gmail.com>
 */
namespace Api\Controller;
use Think\Controller;
use Api\Model\Upload;

class UtilController extends ApiBasicController {

	/**
	 * 工具模块
	 */
	public $utilModel;

    public $uploadModel;

    protected function _initialize(){
        $this->utilModel     = D('util');
        $this->uploadModel   = D('Upload');
        $this->userInfoModel = D('UserInfo');
        $this->userModel     = D('User');
        $this->orderModel    = D('Order');
        $this->orderShipModel = D('OrderShip');
        $this->productImageModel = D('ProductImage');
    }

    /**
     * 上传图片
     */
    public function uploadFile(){

        if (!IS_POST) json_msg(L('ERROR_PARAM'), 1);

        $tmp = array(
                'server' => $_SERVER,
                'request' => I('request.'),
                'file' => $_FILES,
            );

        @file_put_contents('Public/tmp/'.time(), var_export($tmp, true));

        $reqData = I('request.');

        $imgRes = $this->uploadModel->uploadFile();

        if (!$imgRes['path']) json_msg(L('ERR_UPLOAD'), 1);

        if ($reqData['type'] == 'avatar') $this->userInfoModel->where(array('user_id' => $reqData['ssid']))->save(array('avatar' => $imgRes['path']));

        $outdata = array(
                'info'      => L('SUCCESS_UPLOAD'),
                'avatar'    => str_replace('.png', '_200_200.png', C('API_WEBSITE').$imgRes['path']),
                'user_info' => array_values($this->userInfoModel->getUserInfo($reqData['ssid'])),
            );

        json_msg($outdata);
    }

    /**
     * 下载图片并裁剪
     */
    public function downloadImg(){
        // $imgList = $this->utilModel->query("select * from ch_product_image where path like '%http%' and gid >= 1389");

        foreach ($imgList as $k => $v) {
            $tmpPath = 'Public/uploads/20151019/'.$v['gid'].'.jpg';
            downloadImage($v['path'], $tmpPath);

            $tmpImg = getimagesize($tmpPath);

            if ($tmpImg['0'] > 0 && $tmpImg['1'] > 0)  {
                $this->utilModel->query("UPDATE ch_product_image SET path = '".$tmpPath."' WHERE gid = '".$v['gid']."' ");
            }
            echo $v['gid']."\r\n";
        }
    }

    /**
     * 
     * @param type item
     */
    public function testMAP(){
        $what = google_geo(I('request.address'));
        echo '<pre>';
        print_r($what);exit();
    }

    /**
     * 处理图片
     */
    public function resizeImg(){
        
        $imgList = $this->utilModel->query("select * from ch_product_image where path like '%Public%' and gid >= 1389");

        foreach ($imgList as $k => $v) {

            $v['path'] = str_replace('/Public/uploads/images/', 'Public/uploads/', $v['path']);
            if (!file_exists($v['path'])) continue;

            $img = resizeImg($v['path'], 180, 132, false);

            $newPath = str_replace('.jpg', '_100_100.jpg', $v['path']);

            imagejpeg($img, $newPath);
        }


    }

    /**
     * 设置token内容
     *
     */
    public function setToken(){

    	// if (!I('request.token')) {
    	// 	json_msg(L('ERROR_PARAM'), 1);
    	// }
        
        $status = $this->utilModel->setToken(I('request.token'));

        if (!$status) {
            json_msg(L('ERROR_SET_TOKEN'), 1);
        }

        $data = array(
                'ssid' => $this->utilModel->getSSID($status, I('request.token')),
            );

        json_msg($data);

    }

    /**
     * 发送用户反馈
     *
     */
    public function feedback(){
        $content = I('get.content', 'htmlspecialchars');
        $content .= '<br/>DeviceToken:'.I('get.token');

        $to = array(
                'all@nijigo.com',
            );
        $subject = 'NijiGo FeedBack';

        send_mail($to, $subject, $content);

        json_msg();
    }

    /**
     * 微信回调
     */
    public function wxpay(){
        
        $xml = xml_to_arr(file_get_contents("php://input"));

        $log = array(
                'xml' => $xml,
            );
        
        write_log($log, 'Util.wxpay');

        if (!isset($xml['result_code']) || !isset($xml['return_code'])) return false;

        if ($xml['result_code'] !== 'SUCCESS') return false;

        $where = array(
                'wx_oid'  => $xml['out_trade_no'],
                'user_id' => $xml['device_info'],
            );

        $orderInfo = $this->orderModel->where($where)->find();
        // 防止重复提示
        if ($orderInfo['pay_status'] == 1) return false;
        
        $save = array(
                'pay_time'   => strtotime($xml['time_end']),
                'pay_status' => 1,
            );

        if ($orderInfo['status'] == 4) $save['status'] = 2;

        $res = $this->orderModel->where($where)->save($save);

        $log = array(
                'old_data'   => $orderInfo,
                'update_sql' => $this->orderModel->getLastSql(),
            );

        write_log($log, 'wxpay_update_190');
        
        $orderInfo = array(
                    'oid'       => $xml['out_trade_no'],
                    'total_fee' => $xml['total_fee'] / 100,
            );

        $this->sendOrderMail($orderInfo);

        $returnXml = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';

        echo $returnXml;
    }

    /**
     * 支付宝回调
     */
    public function alipay(){
        $reqData = I('request.');
        
        write_log($reqData, 'Util.alipay');

        if (!isset($reqData['trade_status']) || !isset($reqData['out_trade_no'])) return false;
        
        if ($reqData['trade_status'] !== 'TRADE_SUCCESS') return false;

        $where = array(
                'oid' => $reqData['out_trade_no'],
            );

        $orderInfo = $this->orderModel->where($where)->find();
        // 防止重复提示
        if ($orderInfo['pay_status'] == 1) return false;
        
        $save = array(
                'pay_time'   => strtotime($reqData['notify_time']),
                'pay_status' => 1,
            );

        if ($orderInfo['status'] == 4) $save['status'] = 2;

        $res = $this->orderModel->where($where)->save($save);

        $log = array(
                'old_data'   => $orderInfo,
                'update_sql' => $this->orderModel->getLastSql(),
            );

        write_log($log, 'alipay_update_225');

        $orderInfo = array(
                    'oid'       => $reqData['out_trade_no'],
                    'total_fee' => $reqData['total_fee'],
            );

        $this->sendOrderMail($orderInfo);
    }

    /**
     * 物流信息
     */
    public function shipInfo(){
        
        $reqData = I('request.');

        $where = array(
                'oid' => $reqData['oid'],
            );

        $shipInfo = $this->orderShipModel->where($where)->find();

        $shipInfo['content'] = unserialize($shipInfo['content']);

        $this->assign('detail', $shipInfo);

        $this->display('Product/shipInfo');
    }

    /**
     * 运单爬虫
     */
    public function fetchShipInfo($shipId = 0){
        // $shipId = 'el033486996jp';
        // $shipId = 'CD232922995JP';
        
        $this->_initSnoopy();

        $fetchUrl = sprintf(C('EMS_JAPAN'), $shipId);

        $html = $this->snoopy->fetch($fetchUrl);

        $returnRes = $this->fetch->fetch($html->results, 'getInfo');

        if (count($returnRes) <= 0) return false;

        $save = array(
                'content' => serialize($returnRes),
                'lasted'  => time(),
            );

        $where = array(
                'sid' => $shipId,
            );

        $this->orderShipModel->where($where)->save($save);
    }

    /**
     * 发送订单付款通知邮件
     * @param array $orderInfo 订单信息
     * @param string $payType 支付方式
     */
    public function sendOrderMail($orderInfo = array(), $payType = 'alipay'){

        $content = "有订单已付款:".$orderInfo['oid']."<br/>支付金额:".$orderInfo['total_fee']."RMB<br><br>支付方式:".$payType."<br><br><a href='http://admin.nijigo.com'>进入管理后台</a>";

        $to = array(
                'stevenwang@nijigo.com',
                'jeffzhang@nijigo.com',
                'zhengyu@nijigo.com',
            );
        $subject = '已付款 - 单号:'.$orderInfo['oid'].' - Nijigo';

        send_mail($to, $subject, $content);
    }

    /**
     * 获取feed
     */
    public function fetchRss(){

        $url = "http://mcha-cn.com/feed";

        $this->_initSnoopy('Freeshop');

        $html = $this->snoopy->fetch($url);

        $html = str_replace("content:encoded", "contentencoded", $html->results);

        $feed = xml_to_arr($html);

        $mchaModel = D('Mcha');

        foreach ($feed['channel']['item'] as $k => $v) {

            $where = array(
                    'link' => $v['link'],
                );

            $hasExists = $mchaModel->where($where)->count();

            if ($hasExists >= 1) continue;

            $description = $this->_splitATag($v['contentencoded']);

            $add = array(
                    'title'       => $v['title'],
                    'description' => htmlspecialchars($description),
                    'link'        => $v['link'],
                    'category'    => serialize($v['category']),
                    'image_id'    => $this->_getRssImage($description),
                    'pub_date'    => strtotime($v['pubDate']),
                    'status'      => 0,
                );

            $mchaModel->add($add);
        }
    }

    /**
     * 获取网页数据
     */
    public function fetchWeb(){
        exit('no pass');
        $argv = $_SERVER["argv"];

        $url = "http://tax-freeshop.jnto.go.jp/eng/locator.php?location=".$argv[2]."&lat=&lng=&keyword=&sort=shop_name_asc&page=%s";

        $this->_initSnoopy('Freeshop');

        $freeshopModel = D('Freeshop');

        for ($i=1; $i < $argv[3]; $i++) { 
            
            $fetchUrl = sprintf($url, $i);

            $html = $this->snoopy->fetch($fetchUrl);

            $returnRes = $this->fetch->fetch($html->results, 'getInfo');

            foreach ($returnRes as $k => $v) {

                if (!isset($v['address'][0]) || empty($v['address'][0])) continue;

                $add = array(
                        'name'     => $v['address'][0],
                        'address'  => serialize($v['address']),
                        'link'     => count($v['link']) <= 0 ? '' : serialize($v['link']),
                        'tel'      => empty($v['tel']) ? '' : $v['tel'],
                        'credit'   => empty($v['credit']) ? '' : $v['credit'],
                        'category' => empty($v['category']) ? '' : $v['category'],
                        'city'     => $argv[4],
                    );

                $freeshopModel->add($add);
            }
        }
    }

    /**
     * 更新免税店
     */
    public function upFressShop(){
        exit('no pass');
        $area = array(
            'Aichi'     => '爱知',
            'Akita'     => '秋田',
            'Aomori'    => '青森',
            'Chiba'     => '千叶',
            'Ehime'     => '爱媛',
            'Fukui'     => '福井',
            'Fukuoka'   => '福冈',
            'Fukushima' => '福岛',
            'Gifu'      => '岐阜',
            'Gunma'     => '群马',
            'Hiroshima' => '广岛',
            'Hokkaido'  => '北海道',
            'Hyogo'     => '兵库',
            'Ibaraki'   => '茨城',
            'Ishikawa'  => '石川',
            'Iwate'     => '岩手',
            'Kagawa'    => '香川',
            'Kagoshima' => '鹿儿岛',
            'Kanagawa'  => '神奈川',
            'Kochi'     => '高知',
            'Kumamoto'  => '熊本',
            'Kyoto'     => '京都',
            'Mie'       => '三重',
            'Miyagi'    => '宫城',
            'Miyazaki'  => '宫崎',
            'Nagano'    => '长野',
            'Nagasaki'  => '长崎',
            'Nara'      => '奈良',
            'Niigata'   => '新泻',
            'Oita'      => '大分',
            'Okayama'   => '冈山',
            'Okinawa'   => '冲绳',
            'Osaka'     => '大阪',
            'Saga'      => '传奇',
            'Saitama'   => '埼玉',
            'Shiga'     => '滋贺',
            'Shimane'   => '岛根',
            'Shizuoka'  => '静冈',
            'Tochigi'   => '枥木',
            'Tokushima' => '德岛',
            'Tokyo'     => '东京',
            'Tottori'   => '鸟取',
            'Toyama'    => '富山',
            'Wakayama'  => '和歌山',
            'Yamagata'  => '山形',
            'Yamaguchi' => '山口',
            'Yamanashi' => '山梨',
            );
        $argv = $_SERVER["argv"];
        $freeshopModel = D('Freeshop');
        $salerModel = D('Saler');

        $shop = $freeshopModel->limit(page($argv[2], 5000))->order('id ASC')->select();

        foreach ($shop as $k => $v) {

            $name = unserialize($v['address']);
            $link = unserialize($v['link']);

            $add = array(
                    'name'       => str_replace(array('(', ')'), '', $name[1]),
                    'name_en'    => $v['name'],
                    'sale_url'  => isset($link[0]) ? $link[0] : '',
                    'address'    => str_replace(array('(', ')'), '', $name[2]),
                    'tel'        => str_replace('Tel:', '', $v['tel']),
                    'category'   => '免税店',
                    'area'       => $area[$v['city']],
                    'status'     => 1,
                    'type'       => 1,
                    'avg_rating' => rand(4, 5),
                );
            
            $salerModel->add($add);
        }

    }

    /**
     * 剥离a标签
     * @param string $content 内容
     */
    public function _splitATag($content = false){

        $content = preg_replace('/<a\s+href="(.*)">/', '<a>', $content);
        $content = preg_replace('/width="\d+"|height="\d+"/', '', $content);

        return $content;
    }

    /**
     * 获取rss图片并下载
     * @param string $content 内容
     */
    public function _getRssImage($content = false){

        $imageUrl = $this->fetch->fetch($content, 'getRssImage');

        if (is_array($imageUrl) && isset($imageUrl[0])) $imageUrl = $imageUrl[0];
        
        $filePath = 'Public/uploads/album/'.mkUUID().'.jpg';

        downloadImage($imageUrl, $filePath);

        if (!file_exists($filePath)) return false;

        $imgObj = slice_image($filePath);

        imagejpeg($imgObj, $filePath);

        $add = array(
                'parent_id' => 0,
                'path'      => '/'.$filePath,
                'type'      => 0,
                'created'   => NOW_TIME,
            );

        $gid = $this->productImageModel->add($add);

        return $gid;
    }

    /**
     * 实例化snoopy
     *
     */
    private function _initSnoopy($className = 'Ems'){

        import('Extend.Snoopy');

        $this->snoopy = new \Snoopy();
        $this->snoopy->agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';

        import('Extend.FetchHTML');

        $this->fetch = new \FetchHTML($className);
    }

}
