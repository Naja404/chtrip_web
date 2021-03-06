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
        $this->userAddressModel = D('UserAddress');
        $this->orderModel = D('Order');
        $this->commentModel = D('Comment');
        $this->commentImageModel = D('CommentImage');
    }

    /**
     * 用户发布评论 2016-4-11
     */
    public function pubComment(){

        $reqData = I('request.');

        $type = I('request.type', 1);

        $where = array(
                'user_id' => $reqData['ssid'],
            );

        $type == 1 ? $where['oid'] = $reqData['id'] : $where['saler_id'] = $reqData['id'];

        $isComment = $this->commentModel->where($where)->count();

        if ($isComment >= 1) {
            if (IS_AJAX) {
                json_msg('评论错误', 1);
                exit();
            }
            $this->display('User/errorComment');
            exit();
        }

        $queryRes = $this->userModel->getCommentType($reqData);

        if (IS_AJAX) {

            $comment = array();

            foreach ($queryRes as $k => $v) {
                
                $commentText = strip_tags($reqData['comment_'.$v['pid']]);

                if (empty($commentText)){
                    json_msg('请填写评论内容', 1);
                    exit();
                }

                $comment[] = array(
                        'comment' => array(
                            'user_id'  => $reqData['ssid'],
                            'type'     => (int)$type,
                            'comment'  => $commentText,
                            'rate'     => $reqData['rate_'.$v['pid']],
                            'oid'      => $type == 1 ? $reqData['id'] : '',
                            'pid'      => isset($v['pid']) ? $v['pid'] : '',
                            'saler_id' => isset($v['saler_id']) ? $v['saler_id'] : '',
                            'created'  => NOW_TIME,
                            ),
                        'image' => array(
                                'path' => $reqData['commentPicValue_'.$v['pid']],
                            ),
                    );
            }

            foreach ($comment as $k => $v) {
                $cid = $this->commentModel->add($v['comment']);
                
                $v['image']['cid'] = $cid;
                
                if ($cid && !empty($v['image']['path'])) $this->commentImageModel->add($v['image']);
            }

            if ($type == 1) $this->orderModel->where(array('oid' => $reqData['id']))->save(array('comment' => 1));

            json_msg();exit;
        }else{
            if (!$queryRes) {
                $this->display('User/errorComment');
            }else{
                $this->assign('detail', $queryRes);
                
                $this->display('User/pubComment');
            }
        }

    }

    /**
     * 获取购物车数量
     */
    public function getCartTotal(){
        $reqData = I('request.');

        $total = $this->userModel->getCartTotal($reqData);

        $outdata = array(
                'cart_total' => (string)$total,
                'has_wantbuy' => $this->userModel->hasWantBuy($reqData),
            );

        json_msg($outdata);
    }

    /**
     * 获取订单
     */
    public function getOrder(){
        
        $reqData = I('request.');

        $order = $this->orderModel->getOrderList($reqData);

        if (is_string($order)) json_msg($order, 1);

        json_msg($order);

    }

    /**
     * 取消订单
     */
    public function cancelOrder(){
        
        $reqData = I('request.');

        $status = $this->orderModel->cancelOrder($reqData);

        if ($status !== true) json_msg($status, 1);

        return $this->getOrder();
    }

    /**
     * 更改支付方式
     */
    public function changePay(){

        $reqData = I('request.');

        $status = $this->orderModel->chackUserPay($reqData);

        if (is_string($status)) json_msg($status, 1);

        $order = A('Api/Order');
        
        $status['pay'] = $reqData['pay'];
        $status['ssid'] = $reqData['ssid'];

        $res = $order->getPayRes($status);

        if (is_string($res)) json_msg($res, 1);

        json_msg($res);
    }

    /**
     * 开始支付
     */
    public function payOrder(){

        $reqData = I('request.');

        $status = $this->userModel->checkUserPay($reqData['ssid'], $reqData);

        if ($status !== true) json_msg($status, 1);

        $order = A('Api/Order');

        $res = $order->createOrder($reqData);

        if (is_string($res)) json_msg($res, 1);

        json_msg($res);
    }

    /**
     * 订单预览
     */
    public function preCheckOut(){
        $reqData = I('request.');

        $queryRes = $this->userModel->preCheckOut($reqData);

        if (!is_array($queryRes)) json_msg($queryRes, 1);

        json_msg($queryRes);
    }

    /**
     * 获取用户收获地址
     */
    public function getAddress(){
        $reqData = I('request.');

        $queryRes = $this->userAddressModel->getUserAddress($reqData['ssid']);

        if (!is_array($queryRes)) json_msg($queryRes, 1);

        $outdata = array(
                'list'    => $queryRes,
                'add_url' => sprintf(C('API_ADD_ADDRESS_URL'), $reqData['ssid'], time()),
            );

        json_msg($outdata);
    }

    /**
     * 添加收货地址
     */
    public function addAddress(){

        if (IS_AJAX) {
            $reqData = I('request.');

            $ajax = array(
                    'status' => '0',
                );

            if ($reqData['type'] == 'city') {
                
                $queryRes = $this->userAddressModel->getCityList($reqData['id'], $reqData['level'], 1);
                $ajax['html'] = $queryRes;

                $this->ajaxReturn($ajax);
            }elseif ($reqData['type'] == 'add') {
                
                $checkRes = $this->userAddressModel->verifyAddress($reqData);

                if ($checkRes !== true) {
                    $ajax = array(
                            'status' => '1',
                            'msg'    => $checkRes,
                        );
                    $this->ajaxReturn($ajax);
                    exit;
                }

                $this->userAddressModel->setAddress($reqData);

                $this->ajaxReturn($ajax);

            }elseif($reqData['type'] == 'edit'){

                $checkRes = $this->userAddressModel->verifyAddress($reqData);

                if ($checkRes !== true) {
                    $ajax = array(
                            'status' => '1',
                            'msg'    => $checkRes,
                        );
                    $this->ajaxReturn($ajax);
                    exit;
                }
                
                $res = $this->userAddressModel->saveAddress($reqData);

                $this->ajaxReturn($ajax);
            }

        }

        $this->assign('cityList', $this->userAddressModel->getCityList());
        $this->display('User/addAddress');
    }

    /**
     * 删除收货地址
     */
    public function delAddress(){
        
        $reqData = I('request.');

        $this->userAddressModel->delAddress($reqData);

        return $this->getAddress();
    }

    /**
     * 编辑收货地址
     */
    public function editAddress(){

        $reqData = I('request.');

        $checkRes = $this->userAddressModel->checkAddress($reqData);

        if ($checkRes !== true) {
            $this->display('User/404');
            exit();
        }
        
        $detail = $this->userAddressModel->getDetail($reqData);

        $this->assign('detail', $detail);
        $this->assign('province', $this->userAddressModel->getCityList());
        $this->assign('city', $this->userAddressModel->getCityList($detail['pid'], 2));
        $this->assign('area', $this->userAddressModel->getCityList($detail['cid'], 3));
        $this->display('User/editAddress');
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

        if (isset($reqData['openid'])) {

            $userSNS = $this->userSNSModel->where(array('openid' => $reqData['openid'], 'summary' => $reqData['state']))->find();


            $userAdd = array(
                    'user_id'  => $ssid,
                    'mobile'   => $reqData['mobile'],
                    'passwd'   => md5($reqData['pwd']),
                    'nickname' => $userSNS['nickname'],
                    'sex'      => $userSNS['sex'],
                    'avatar'   => $userSNS['headimgurl'],
                );

            if ($userSNS['headimgurl']) $userAdd['avatar'] = $this->getImgWithURL($userSNS['headimgurl']);

        }else{
            $userAdd = array(
                    'user_id'  => $ssid,
                    'mobile'   => $reqData['mobile'],
                    'passwd'   => md5($reqData['pwd']),
                    'nickname' => $reqData['mobile'],
                    'sex'      => 0,
                );
        }

        $insertID = $this->userInfoModel->add($userAdd);

        if ($insertID && isset($userSNS['openid'])) {
            $this->userSNSModel->where(array('openid' => $reqData['openid'], 'summary' => $reqData['state']))->save(array('user_id' => $userAdd['user_id']));
        }

        $outdata = array(
                'ssid'     => $ssid,
                'info'     => L('SUCCESS_REGISTER'),
                'nickname' => $userAdd['mobile'],
                'user_info' => array_values($this->userInfoModel->getUserInfo($ssid)),
            );
        $outdata['hasBand'] = $outdata['user_info'][2] == L('TEXT_MOBILE_NOT_BIND') ? '0' : '1';

        // 迁移 游客数据至注册用户
        // $this->userModel->mergeUserInfo($reqData['ssid'], $ssid);

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

        if (isset($reqData['openid'])) {
            $snsWhere = array('openid' => $reqData['openid'], 'summary' => $reqData['state']);
            $this->userSNSModel->where($snsWhere)->save(array('user_id' => $userInfo['user_id']));
        }

        $outdata = array(
                'ssid'      => $userInfo['user_id'],
                'nickname'  => $userInfo['nickname'],
                'avatar'    => C('API_WEBSITE').$userInfo['avatar'],
                'info'      => L('SUCCESS_LOGIN'),
                'user_info' => array_values($this->userInfoModel->getUserInfo($userInfo['user_id'])),
            );
        
        $outdata['hasBand'] = $outdata['user_info'][2] == L('TEXT_MOBILE_NOT_BIND') ? '0' : '1';

        // 迁移 游客数据至注册用户
        // $this->userModel->mergeUserInfo($reqData['ssid'], $userInfo['user_id']);

        json_msg($outdata);

    }

    /**
     * 设置用户信息
     */
    public function setInfo(){
        $reqData = I('request.');

        if (!$this->userModel->checkSSID($reqData['ssid'])) json_msg(L('ERROR_PARAM'), 1);

        $setErr = $this->_checkSetInfo($reqData);

        if (count($setErr) <= 0 || is_string($setErr)) json_msg($setErr, 1);

        $where = array(
                'user_id' => $reqData['ssid'],
            );

        $this->userInfoModel->where($where)->save($setErr);

        $outdata = $this->userInfoModel->getUserInfo($reqData['ssid']);

        $outdata['user_info'] = array_values($outdata);
        $outdata['info'] = L('SUCCESS_UPDATE');
        $outdata['avatar'] = C('API_WEBSITE').$outdata['avatar'];

        $outdata['hasBand'] = $outdata['mobile'] == L('TEXT_MOBILE_NOT_BIND') ? '0' : '1';

        json_msg($outdata);
    }

    /**
     * 微信登录
     */
    public function loginWeChat(){
        
        $reqData = I('request.');

        if ($reqData['errcode'] == '-4') json_msg('您已拒绝授权', 1);

        if ($reqData['errcode'] == '-2') json_msg('您已取消', 1);

        if (empty($reqData['code'])) json_msg('授权失败', 1);

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx140bb397338ea49a&secret=234f110c04e124980eaa3dad81740da1&code=%s&grant_type=authorization_code";

        $tokenRes = json_decode(file_get_contents(sprintf($url, $reqData['code'])), true);

        if (!isset($tokenRes['access_token']) || empty($tokenRes['access_token'])) json_msg('授权失败', 1);

        $userInfoUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s";

        $userSNS = $this->userSNSModel->where(array('openid' => $tokenRes['openid'], 'summary' => $reqData['state']))->find();

        // 没有授权过,没有关联账户
        if (!$userSNS['openid']) {

            $weChatInfo = json_decode(file_get_contents(sprintf($userInfoUrl, $tokenRes['access_token'], $tokenRes['openid'])), true);

            if ($weChatInfo['errcode']) json_msg('授权失败', 1);

            $ssid = $this->checkSSID($reqData['ssid']);

            $add = array(
                    'user_id'      => $ssid,
                    'code'         => $reqData['code'],
                    'summary'      => $reqData['state'],
                    'access_token' => $tokenRes['access_token'],
                    'openid'       => $weChatInfo['openid'],
                    'nickname'     => $weChatInfo['nickname'],
                    'sex'          => $weChatInfo['sex'],
                    'province'     => $weChatInfo['province'],
                    'city'         => $weChatInfo['city'],
                    'country'      => $weChatInfo['country'],
                    'headimgurl'   => $weChatInfo['headimgurl'],
                );

            $this->userSNSModel->add($add);

            $userId = $add['user_id'];

            $this->setUserInfoWithSNS($add);

        }else{
            // 授权,检测是否关联过账户
            if ($userSNS['user_id']) {
                $userId = $userSNS['user_id'];
            }else{
                $ssid = $this->checkSSID();

                $this->userSNSModel->where(array('openid' => $userSNS['openid'], 'summary' => $reqData['state']))
                                   ->save(array('user_id' => $ssid));
                $userId = $ssid;

                $setInfo = array(
                        'user_id' => $userId,
                    );

                $this->setUserInfoWithSNS($setInfo);
            }
        }
        
        // 迁移 游客数据至注册用户
        // $this->userModel->mergeUserInfo($reqData['ssid'], $userId);

        $this->_returnUserInfo($userId);
    }

    /**
     * 根据第三方登陆设置用户信息
     * @param array $setInfo 信息内容
     */
    public function setUserInfoWithSNS($setInfo = array()){

        $where = array('user_id' => $setInfo['user_id']);

        $userInfo = $this->userInfoModel->where($where)->find();

        if ($userInfo['user_id']) {
            
            $update = array();

            if (empty($userInfo['nickname'])) $update['nickname'] = $setInfo['nickname'];

            if (empty($userInfo['avatar'])) $update['avatar'] = $this->getImgWithURL($setInfo['headimgurl']);

            if (count($update)) $this->userInfoModel->where($where)->save($update);

        }else{
            $add = array(
                    'user_id'  => $setInfo['user_id'],
                    'mobile'   => '0',
                    'passwd'   => md5('000000'),
                    'nickname' => $setInfo['nickname'] ? $setInfo['nickname'] : '',
                    'avatar'   => $this->getImgWithURL($setInfo['headimgurl']),
                    'sex'      => $setInfo['sex'] ? (int)$setInfo['sex'] : '0',
                );

            $this->userInfoModel->add($add);
        }
    }

    /**
     * 根据url获取图片
     */
    public function getImgWithURL($url = false){

        if (!$url) return '';

        $tmpPath = 'Public/uploads/images/20151105/'.time().'.png';

        downloadImage($url, $tmpPath);

        downloadImage($url, str_replace('.png', '_200_200.png', $tmpPath));

        return '/'.$tmpPath;
    }

    /**
     * 获取购物车列表
     */
    public function getCart(){
        
        $returnRes = $this->userModel->getCart(I('request.ssid'));

        json_msg($returnRes);
    }

    /**
     * 更新购物车
     */
    public function setCart(){
        $reqData = array(
                'ssid'     => I('request.ssid'),
                'type'     => I('request.type'),
                'pid'      => I('request.pid'),
            );

        $returnRes = $this->userModel->setCart($reqData);

        json_msg($returnRes);
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
     * 添加产品到购物车
     */
    public function addCart(){
        $reqData = array(
                'ssid' => I('request.ssid'),
                'pid'  => I('request.pid'),
            );

        $returnRes = $this->userModel->addCart($reqData);

        if (is_string($returnRes)) json_msg($returnRes, 1);

        $total = $this->userModel->getCartTotal($reqData);

        $outdata = array(
                'cart_total' => (string)$total,
            );

        json_msg($outdata);
    }

    /**
     * 添加产品到扫货清单
     */
    public function addBuyList(){

        $reqData = array(
                'ssid' => I('request.ssid'),
                'pid'  => I('request.pid'),
                'type' => isset($_REQUEST['type']) ? I('request.type') : 1,
            );

        $returnRes = $this->userModel->addBuyList($reqData);

        if (is_string($returnRes)) {
            json_msg($returnRes, 1);
        }

        json_msg($returnRes);
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
     * 获取 收藏专辑 列表
     */
    public function getAlbumList(){
        
        $returnRes = $this->userModel->getAlbumList(I('request.ssid'));

        if (count($returnRes) > 0 ) {
            json_msg($returnRes);
        }

        json_msg();
    }

    /**
     * 获取 收藏列表
     */
    public function getFavoriteList(){

        $ssid = I('request.ssid');

        $wantGo = $this->userModel->getWantList($ssid);

        $wantBuy = $this->userModel->getBuyList($ssid);

        $album = $this->userModel->getAlbumList($ssid);

        $returnRes = array(
                'want_go'  => count($wantGo) <= 0 ? array() : $wantGo,
                'want_buy' => count($wantBuy) <= 0 ? array() : $wantBuy,
                'album'    => count($album) <= 0 ? array() : $album,
            );

        json_msg($returnRes);

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
     * 设置 我想去
     */
    public function setWantGo(){
        $reqData = array(
                'ssid' => I('request.ssid'),
                'sid'  => I('request.sid'),
            );
        
        $returnRes = $this->userModel->setWantGo($reqData);

        if (is_string($returnRes)) {
            json_msg($returnRes, 1);
        }

        return $this->getWantList();
    }

    /**
     * 设置 专辑  收藏
     */
    public function setAlbum(){
        
        $reqData = array(
                'ssid'   => I('request.ssid'),
                'aid'    => I('request.aid'),
                'type'   => I('request.type', 1), // 1.添加 2.删除
                'islist' => I('request.islist', 0),
            );

        $returnRes = $this->userModel->setAlbum($reqData);

        if (is_string($returnRes)) {
            json_msg($returnRes, 1);
        }

        if ($reqData['islist']) return $this->getAlbumList();

        json_msg();
    }

    /**
     * 是否收藏 专辑
     */
    public function isCollectAlbum(){
        
        $reqData = I('request.');

        $isCollect = $this->userModel->isCollectAlbum($reqData);

        json_msg($isCollect);
    }

    /**
     * 检测ssid
     * @param array $reqData 请求数据
     */
    public function checkSSID($reqData = array()){
        
        if (!isset($reqData['ssid'])) {
            $ssid = $this->userModel->createSSID();
            return $ssid;
        }

        $hasSSID = $this->userModel->checkSSID($reqData['ssid']);

        if (!$hasSSID) {
            $ssid = $this->userModel->createSSID();
            return $ssid;
        }

        if ($this->userInfoModel->checkUserInfo($reqData['ssid']) > 0) {
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

        if (isset($reqData['mobile'])) {
            if (!check_mobile($reqData['mobile'])) return L('ERR_MOBILE');

            if ($this->userInfoModel->where(array('mobile' => $reqData['mobile']))->count() > 0) return L('ERR_MOBILE_EXISTS');

            $save['mobile'] = $reqData['mobile'];
        }

        if (count($save) <= 0) return L('ERROR_PARAM');

        return $save;
    }

    /**
     * 返回用户数据
     */
    private function _returnUserInfo($user_id = false){
        
        $userInfo = $this->userInfoModel->where(array('user_id' => $user_id))->find();

        $outdata = array(
                'ssid'      => $user_id,
                'nickname'  => $userInfo['nickname'],
                'avatar'    => C('API_WEBSITE').$userInfo['avatar'],
                'info'      => L('SUCCESS_LOGIN'),
                'user_info' => array_values($this->userInfoModel->getUserInfo($user_id)),
            );
        
        $outdata['hasBand'] = $outdata['user_info'][2] == L('TEXT_MOBILE_NOT_BIND') ? '0' : '1';

        json_msg($outdata);
    }
}
