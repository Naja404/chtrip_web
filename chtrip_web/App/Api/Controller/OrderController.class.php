<?php
/**
 * api order 订单模块
 */
namespace Api\Controller;
use Think\Controller;

class OrderController extends ApiBasicController {

	/**
	 * 订单模块
	 */
	public $orderModel;

    protected function _initialize(){
        $this->orderModel = D('Order');
        $this->userModel = D('User');
        $this->orderDetailModel = D('OrderDetail');
        $this->productModel = D('ProductsCopy');
        $this->userBuyModel = D('UserBuylist');
    }

    /**
     * 创建订单
     * @param array $reqData
     */
    public function createOrder($reqData = array()){

        $payInfo = L('ERR_CREATED_ORDER');

        $preOrderInfo = $this->_preCreateOrder($reqData);

        if ($preOrderInfo === false || count($preOrderInfo) <= 0) return $payInfo;

        if ($reqData['pay'] == 'wxpay') {
            $payInfo = $this->getWXPayKey($preOrderInfo);
        }

        if ($reqData['pay'] == 'alipay') {
            $payInfo = $this->getAlipayKey($preOrderInfo);
        }

        if (!is_string($payInfo)) $this->clearCart($reqData['ssid']);

        return $payInfo;
    }

    /**
     * 清空购物车
     * @param string $userId 用户id
     */
    public function clearCart($userId = false){
        
        $where = array(
                'user_id' => $userId,
            );

        $save = array(
                'cart' => '',
            );

        return $this->userBuyModel->where($where)->save($save);
    }

    /**
     * 获取微信支付接口数据并返回
     * @param array $reqData 请求数据内容
     */
    public function getWXPayKey($reqData = array()){
        $reqData['total_fee'] = 0.01;
        $wxpayReq = array(
                'appid'            => C('WXPAY_CONF.APP_ID'),
                'body'             => '彩虹Go-商品',
                'device_info'      => $reqData['ssid'],
                'fee_type'         => C('WXPAY_CONF.FEE_TYPE'),
                'limit_pay'        => C('WXPAY_CONF.LIMIT_PAY'),
                'mch_id'           => C('WXPAY_CONF.MCH_ID'),
                'nonce_str'        => strtoupper(md5($reqData['ssid'].time())),
                'notify_url'       => C('WXPAY_CONF.NOTIFY_URL'),
                'out_trade_no'     => $reqData['oid'],
                'product_id'       => $reqData['pid'],
                'spbill_create_ip' => C('SERVER_IP'),
                'total_fee'        => $reqData['total_fee'] * 100,
                'trade_type'       => C('WXPAY_CONF.TRADE_TYPE'),
            );
        
        $wxpayReq['sign'] = set_wx_sign($wxpayReq);
        
        write_log($wxpayReq, 'wxpayReq_69');

        $xmlData = format_xml($wxpayReq);

        $respData = xml_to_arr(put_curl(C('WXPAY_CONF.REQ_URL'), $xmlData));

        write_log($respData, 'wxrespData_75');

        if ($respData['return_code'] != 'SUCCESS') return L('ERR_USER_INFO');

        $output = array(
                'appid'     => $respData['appid'],
                'noncestr'  => $wxpayReq['nonce_str'],
                'package'   => 'Sign=WXPay',
                'partnerid' => $respData['mch_id'],
                'prepayid'  => $respData['prepay_id'],
                'timestamp' => time(),
            );

        $output['sign'] = set_wx_sign($output);

        write_log($output, 'wx_output_90');

        return $output;
    }

    /**
     * 获取支付宝支付接口数据
     * @param array $reqData 请求数据内容
     */
    public function getAlipayKey($reqData = array()){

        $wap = $this->_confAlipay($reqData, 'WAP');
        $app = $this->_confAlipay($reqData, 'APP');

        $output = array(
                'wap_url' => C('ALIPAY_CONF.WAP_URL').createLinkstringUrlencode($wap),
                'app_url' => createLinkstringUrlencode($app, true),
            );

        return $output;
    }

    /**
     * 支付宝配置信息
     * @param string $payType 支付类型 app,wap
     */
    private function _confAlipay($order = array(), $payType = 'WAP'){
        $order['total_fee'] = '0.01';
        $conf = array(
                'partner'             => C('ALIPAY_CONF.PARTNER_ID'),
                'seller_id'           => C('ALIPAY_CONF.SELLER_ID'),
                'private_key_path'    => C('ALIPAY_CONF.PATH').'rsa_private_key.pem',
                'ali_public_key_path' => C('ALIPAY_CONF.PATH').'alipay_public_key.pem',
                'sign_type'           => 'RSA',
                'input_charset'       => 'utf-8',
                'cacert'              => getcwd().'\\cacert.pem',
                'transport'           => 'http',
                'payType'             => $payType,
            );



        if ($payType == 'WAP') {
            $param = array(
                    'partner'        => C('ALIPAY_CONF.PARTNER_ID'),
                    'seller_id'      => C('ALIPAY_CONF.SELLER_ID'),
                    'out_trade_no'   => $order['oid'],
                    'subject'        => '彩虹Go-商品',
                    'body'           => '彩虹Go-商品详情',
                    'total_fee'      => $order['total_fee'],
                    'notify_url'     => C('ALIPAY_CONF.NOTIFY_URL'),
                    'return_url'     => C('ALIPAY_CONF.NOTIFY_URL'),
                    'service'        => C('ALIPAY_CONF.SERVICE_'.$payType),
                    'payment_type'   => '1',
                    '_input_charset' => 'utf-8',
                    'it_b_pay'       => '30m',
                    'show_url'       => 'http://www.nijigo.com',
                );
        }else{
            $param = array(
                    'partner'        => '"'.C('ALIPAY_CONF.PARTNER_ID').'"',
                    'seller_id'      => '"'.C('ALIPAY_CONF.SELLER_ID').'"',
                    'out_trade_no'   => '"'.$order['oid'].'"',
                    'subject'        => '"彩虹Go-商品"',
                    'body'           => '"彩虹Go-商品详情"',
                    'total_fee'      => '"'.$order['total_fee'].'"',
                    'notify_url'     => '"'.C('ALIPAY_CONF.NOTIFY_URL').'"',
                    'service'        => '"'.C('ALIPAY_CONF.SERVICE_'.$payType).'"',
                    'payment_type'   => '"1"',
                    '_input_charset' => '"utf-8"',
                    'it_b_pay'       => '"30m"',
                    'show_url'       => '"m.alipay.com"',
                );
        }

        $param = alipay_sign($param, $conf);

        return $param;

    }

    /**
     * 预创建Order
     * @param array $reqData 请求数据内容
     */
    private function _preCreateOrder($reqData = array()){
        
        $cartInfo = $this->userModel->getCart($reqData['ssid']);
        $totalInfo = $this->userModel->preCheckOut($reqData);

        $order = array(
                'oid'        => $this->orderModel->makeOrderId(),
                'user_id'    => $reqData['ssid'],
                'ship_type'  => $reqData['ship'],
                'ship_fee'   => $totalInfo['shipping_price'],
                'weight'     => $totalInfo['weight_total'],
                'pay_type'   => $reqData['pay'] == 'wxpay' ? '1' : '2',
                'pay_fee'    => $totalInfo['price_total'],
                'total_fee'  => $totalInfo['price_total'],
                'pay_time'   => 0,
                'created'    => time(),
                'pay_status' => 0,
                'status'     => 4,
            );

        $res = $this->orderModel->add($order);

        if (!$res) return false;

        $addAll = array();

        foreach ($cartInfo['list'] as $k => $v) {
            $where = array(
                    'pid' => $v['pid'],
                );

            $query = $this->productModel->field('image_id')->where($where)->find();

            $addAll[] = array(
                    'oid'      => $order['oid'],
                    'pid'      => $v['pid'],
                    'image_id' => $query['image_id'],
                    'title_zh' => $v['title_zh'],
                    'price_zh' => $v['price_zh'],
                    'price_jp' => $v['price_jp'],
                    'weight'   => $v['weight'],
                    'quantity' => $v['total'],
                    'created'  => time(),
                );

            $order['pid'] = $v['pid'];
        }

        $res = $this->orderDetailModel->addAll($addAll);

        if (!$res) return false;

        return $order;
    }

}
