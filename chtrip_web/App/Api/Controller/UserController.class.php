<?php
/**
 * api User 用户模块
 */
namespace Api\Controller;
use Think\Controller;

class UserController extends ApiBasicController {

    /**
     * user model
     */
    public $userModel;

    protected function _initialize(){

        // parent::_initialize();

        $this->userModel = D('User');
        $this->userSNSModel = D('UserSns');

    }

    /**
     * 微信登录
     *
     */
    public function loginWeChat(){

        $reqData = array(
                'ssid'    => I('request.ssid'),
                'errcode' => I('request.errcode'),
                'code'    => I('request.code'),
                'state'   => I('request.state'),
                'lang'    => I('request.lang'),
                'country' => I('request.country'),
            );

        $userInfo = $this->userSNSModel->where(array('user_id' => $reqData['ssid'], 'summary' => $reqData['state']))->find();

        if ($userinfo['openid']) json_msg($userinfo);

        if ($reqData['errcode'] == '-4') json_msg('您已拒绝授权', 1);

        if ($reqData['errcode'] == '-2') json_msg('您已取消', 1);

        if (empty($reqData['code'])) json_msg('授权失败', 1); 

        $add = array(
                'user_id' => $reqData['ssid'],
                'code'    => $reqData['code'],
                'summary' => $reqData['state'],
                'created' => time(),
            );

        $reqData['sns_id'] = $this->userSNSModel->add($add);

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxaa9423c2b77e86bb&secret=90bcc16b9393971027300301a5e79a33&code=%s&grant_type=authorization_code";

        $tokenRes = json_decode(file_get_contents(sprintf($url, $reqData['code'])), true);

        if (!isset($tokenRes['access_token']) || empty($tokenRes['access_token'])) json_msg('授权失败', 1);

        $userInfoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s";

        $userInfo = json_decode(file_get_contents(sprintf($userInfoUrl, $tokenRes['access_token'], $tokenRes['openid'])), true);

        if ($userInfo['errcode']) json_msg('授权失败', 1);

        $update = array(
                'access_token' => $tokenRes['access_token'],
                'openid'       => $userInfo['openid'],
                'nickname'     => $userInfo['nickname'],
                'sex'          => $userInfo['sex'],
                'province'     => $userInfo['province'],
                'city'         => $userInfo['city'],
                'country'      => $userInfo['country'],
                'headimgurl'   =>  $userInfo['headimgurl'],
            );

        $this->userSNSModel->where(array('id' => $reqData['sns_id']))->save($update);

        json_msg($userInfo);

    }

    /**
     * 获取扫货清单
     */
    public function getBuyList(){

        $returnRes = $this->userModel->getBuyList(I('request.ssid'));

        if (count($returnRes) > 0) {
            json_msg($returnRes);
        }

        json_msg();
    }

    /**
     * 更新扫货清单选中状态
     */
    public function setBuyList(){
        $reqData = array(
                'ssid'     => I('request.ssid'),
                'type'     => I('request.type'),
                'pid'      => I('request.pid'),
            );

        $returnRes = $this->userModel->setBuyList($reqData);

        if (count($returnRes) > 0) {
            json_msg($returnRes);
        }

        json_msg();
    }

    /**
     * 添加产品到扫货清单
     */
    public function addBuyList(){

        $reqData = array(
                'ssid' => I('request.ssid'),
                'pid' => I('request.pid'),
            );

        $returnRes = $this->userModel->addBuyList($reqData);

        if (is_string($returnRes)) {
            json_msg($returnRes, 1);
        }

        json_msg();
    }

    /**
     * 获取 我想去 列表
     */
    public function getWantList(){
        $returnRes = $this->userModel->getWantList(I('request.ssid'));

        if (count($returnRes) > 0) {
            json_msg($returnRes);
        }

        json_msg();   
    }

    /**
     * 添加 我想去
     * @param int $sid
     */
    public function addWantGo(){
        $reqData = array(
                'ssid' => I('request.ssid'),
                'sid'  => I('request.sid'),
            );
        
        $returnRes = $this->userModel->addWantGo($reqData);

        if (is_string($returnRes)) {
            json_msg($returnRes, 1);
        }

        json_msg();
    }

}
