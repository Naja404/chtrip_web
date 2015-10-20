<?php
/**
 * api util 使用工具模块
 * @author Hisoka <hisoka.2pac@gmail.com>
 */
namespace Api\Controller;
use Think\Controller;

class UtilController extends ApiBasicController {

	/**
	 * 工具模块
	 */
	public $utilModel;

    protected function _initialize(){
        $this->utilModel = D('util');
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

    	if (!I('request.token')) {
    		json_msg(L('ERROR_PARAM'), 1);
    	}
        
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
                '326101710@qq.com',
            );
        $subject = 'NijiGo FeedBack';

        send_mail($to, $subject, $content);

        json_msg();
    }

}
