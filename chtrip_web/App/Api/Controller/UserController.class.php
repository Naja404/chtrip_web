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
        $this->userInfoModel = D('userInfo');
        $this->userSNSModel = D('UserSns');

    }

    /**
     * 用户注册
     * @param int $mobile
     * @param string @passwd
     */
    public function register(){

        $reqData = I('request.');
        
        $regErr = $this->_checkReg($reqData);

        if ($regErr !== true) json_msg($regErr, 1);

        if ($this->userModel->checkMobile($reqData['mobile'])) json_msg(L('ERR_MOBILE_EXISTS'), 1);

        $ssid = $this->checkSSID($reqData['ssid']);

        $userAdd = array(
                'user_id'  => $ssid,
                'mobile'   => $reqData['mobile'],
                'passwd'   => md5($reqData['pwd']),
                'nickname' => $reqData['mobile'],
                'sex'      => 0,
            );

        $insertID = $this->userInfoModel->add($userAdd);

        $outdata = array(
                'ssid'     => $ssid,
                'info'     => L('SUCCESS_REGISTER'),
                'nickname' => $reqData['mobile'],
                'user_info' => array_values($this->userInfoModel->getUserInfo($ssid)),
            );

        json_msg($outdata);
    }

    /**
     * 用户登录
     */
    public function login(){
        
        $reqData = I('request.');

        $where = array(
                'mobile' => $reqData['mobile'],
                'passwd' => md5($reqData['pwd']),
            );

        $hasAccount = $this->userInfoModel->where($where)->count();

        if ($hasAccount != 1) json_msg(L('ERR_LOGIN'), 1);

        $userInfo = $this->userInfoModel->where($where)->find();

        if (!$userInfo['user_id']) json_msg(L('ERR_LOGIN'), 1);

        $this->userModel->upLoginStatus($ssid);

        $outdata = array(
                'ssid'      => $userInfo['user_id'],
                'nickname'  => $userInfo['nickname'],
                'avatar'    => C('API_WEBSITE').$userInfo['avatar'],
                'info'      => L('SUCCESS_LOGIN'),
                'user_info' => array_values($this->userInfoModel->getUserInfo($userInfo['user_id'])),
            );

        json_msg($outdata);

    }

    /**
     * 设置用户信息
     */
    public function setInfo(){
        $reqData = I('request.');

        if (!$this->userModel->checkSSID($reqData['ssid'])) json_msg(L('ERROR_PARAM'), 1);

        $setErr = $this->_checkSetInfo($reqData);

        if (count($setErr) <= 0){
            json_msg(L('ERROR_PARAM'), 1);
        }
        $where = array(
                'user_id' => $reqData['ssid'],
            );

        $this->userInfoModel->where($where)->save($setErr);

        $outdata = $this->userInfoModel->getUserInfo($reqData['ssid']);

        $outdata['user_info'] = array_values($outdata);
        $outdata['info'] = L('SUCCESS_UPDATE');
        $outdata['avatar'] = C('API_WEBSITE').$outdata['avatar'];

        json_msg($outdata);
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

    /**
     * 检测ssid
     * @param array $reqData 请求数据
     */
    public function checkSSID($reqData = array()){
        
        $hasSSID = $this->userModel->checkSSID($reqData['ssid']);

        if (!$hasSSID) {
            $ssid = $this->userModel->createSSID();
            return $ssid;
        }

        if ($this->userInfoModel->checkUserInfo($reqData['ssid']) > 1) {
            $ssid = $this->userModel->createSSID();
            return $ssid;
        }

        return $reqData['ssid'];
    }

    /**
     * 检测注册值
     * @param array $reqData 注册内容
     */
    private function _checkReg($reqData = array()){
        if (isset($reqData['mobile'])){
            if (!check_mobile($reqData['mobile'])) return L('ERR_MOBILE');
        }else{
            return L('ERR_EMPTY_MOBILE');
        }

        if (isset($reqData['pwd'])) {
            if (!check_pwd($reqData['pwd'])) return L('ERR_PWD');
        }else{
            return L('ERR_EMPTY_PWD');
        }

        return true;
    }

    /**
     * 验证用户信息
     * @param array $reqData
     */
    private function _checkSetInfo($reqData = array()){

        $sava = array();

        if (isset($reqData['nickname'])) {
            $save['nickname'] = $reqData['nickname'];
        }

        if (isset($reqData['sex'])) {
            if (in_array($reqData['sex'], array('0', '1', '2'))) {
                $save['sex'] = (int)$reqData['sex'];
            }
        }

        return $save;
    }

}
