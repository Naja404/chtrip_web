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

}
