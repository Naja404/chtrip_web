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
     */
    public function sendOrderMail($orderInfo = array()){

        $content = "有订单已付款:".$orderInfo['oid']."<br/>支付金额:".$orderInfo['total_fee']."RMB<br><br><a href='http://admin.nijigo.com'>进入管理后台</a>";

        $to = array(
                'stevenwang@nijigo.com',
                'jeffzhang@nijigo.com',
                'zhengyu@nijigo.com',
            );
        $subject = '已付款 - 单号:'.$orderInfo['oid'].' - Nijigo';

        send_mail($to, $subject, $content);
    }

    /**
     * 实例化snoopy
     *
     */
    private function _initSnoopy(){

        import('Extend.Snoopy');

        $this->snoopy = new \Snoopy();
        $this->snoopy->agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';

        import('Extend.FetchHTML');

        $this->fetch = new \FetchHTML('Ems');
    }

}
