<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UserModel extends Model{

	/**
	 * 获取待评价内容
	 * @param array $reqData 请求内容
	 */
	public function getCommentType($reqData = array()){
		if ($reqData['type'] == 1) {
			
			$where = array(
					'oid'     => $reqData['id'],
					'user_id' => $reqData['ssid'],
				);

			$order = $this->table(tname('order'))->where($where)->find();

			if ($order['status'] != 1 && $order['comment'] != 0) return false;

			$where = array(
					'A.oid' => $reqData['id'],
				);

			$queryRes = $this->table(tname('order_detail').' AS A')
				 ->join(tname('product_image').' AS B ON B.gid = A.image_id')
				 ->where($where)
				 ->select();
		}else{

			$where = array(
					'saler_id' => $reqData['id'],
				);

			$queryRes = $this->table(tname('saler'))->where($where)->find();
		}

		return $queryRes;
	}

	/**
	 * 获取购物车商品数量
	 * @param array $reqData 请求内容
	 */
	public function getCartTotal($reqData = array()){
		$where = array(
				'user_id' => $reqData['ssid'],
			);

		$queryRes = $this->table(tname('user_buylist'))->where($where)->find();

		$total = unserialize($queryRes['cart']);

		if (count($total) <= 0) return 0;

		foreach ($total as $k => $v) {
			$tmp += $v['total'];
		}

		if ($tmp <= 0) return 0;

		return $tmp;
	}

	/**
	 * 检测用户支付
	 * @param string $userId 用户id
	 * @param array $reqData 请求内容
	 */
	public function checkUserPay($userId = false, $reqData = array()){

		// 检测用户状态
		$whereAddress = array(
				'id'      => $reqData['aid'],
				'user_id' => $userId,
				'status'  => 1,
			);

		$hasAddress = $this->table(tname('user_address'))->where($whereAddress)->find();
		// 验证订单地址
		if ((int)$hasAddress !== 1) return L('ERR_NO_ADDRESS');

		$cartInfo = $this->getCart($userId);
		// 验证订单商品中状态
		foreach ($cartInfo['list'] as $k => $v) {
			if ($v['status'] != '1') return L('ERR_OUT_OF_SALER');
		}
		
		return true;
	}



	/**
	 * 结算预览
	 * @param array $reqData 查询内容
	 */
	public function preCheckOut($reqData = array()){

		if (!$reqData['ssid']) return L('ERROR_PARAM');

		$address     = $this->_getDefaultAddress($reqData['ssid'], (int)$reqData['aid']);
		$proPrice    = $this->_getCartPrice($reqData['ssid']);
		$addressShip = $this->_getShipping($proPrice['weight_total'], $reqData['ship']);

		if ((int)$proPrice['price_zh_total'] == 0 || (int)$proPrice['weight_total'] == 0) return L('ERROR_PARAM');

		//设置服务税 
		$proPrice['price_zh_total'] = $this->_setPriceTax($proPrice['price_zh_total']);

		$tmpPrice = $proPrice['price_zh_total'] + $addressShip['ship_price'];

		$returnRes = array(
				'address'             => $address,
				'product_price_total' => get_price($proPrice['price_zh_total']),
				'weight_total'        => $proPrice['weight_total'],
				'shipping_type'       => $addressShip['list'],
				'shipping_price'	  => $addressShip['ship_price'],
				'price_total' 		  => get_price($tmpPrice), 
			);

		return $returnRes;
	}

	/**
	 * 检测ssid
	 * @param string $ssid
	 */
	public function checkSSID($ssid = false){
		
		$ssid = $this->where(array('user_id' => $ssid))->getField('user_id');

		if (is_string($ssid) && strlen($ssid) == 32) return true; 

		return false;
	}

	/**
	 * 生成ssid
	 */
	public function createSSID(){
		$ssid = mkUUID();

		$add = array(
				'user_id'         => $ssid,
				'uuid'            => md5($ssid),
				'status'          => 1,
				'created'         => NOW_TIME,
				'last_login_time' => NOW_TIME,
			);

		$this->add($add);

		return $ssid;
	}

	/**
	 * 更新登录信息
	 * @param string $ssid 用户id
	 */
	public function upLoginStatus($ssid = false){
		$where = array(
				'user_id' => $ssid,
			);

		$update = array(
				'last_login_time' => NOW_TIME,
			);

		$this->where($where)->save($update);
	}

	/**
	 * 检测手机号
	 * @param int $mobile
	 */
	public function checkMobile($mobile = false){
		$count = $this->table(tname('user_info'))->where(array('mobile' => $mobile))->count();

		return $count > 0 ? true : false;
	}

	/**
	 * 微信登陆
	 * @param array $reqData 请求数据
	 */
	public function loginWeChat($reqData = array()){

		return $reqData;

	}

	/**
	 * 获取购物车
	 * @param string $userId 用户id
	 */
	public function getCart($userId = false){
		$where = array(
				'user_id' => $userId,
			);

		$cartId = $this->table(tname('user_buylist'))->where($where)->find();

		$cartId = unserialize($cartId['cart']);

		if (!is_array($cartId) || count($cartId) <= 0) return $this->_cartInfo();

		$cartIdArr = array_keys($cartId);

		$proId = implode(",", $cartIdArr);

		$sql = "SELECT 
					A.pid, 
					A.status, 
					CONCAT('".C('API_WEBSITE')."', REPLACE(C.path, '.', '_100_100.')) AS thumb,
					B.title_zh, 
					B.summary_zh, 
					B.price_zh, 
					B.price_jp, 
					A.rest, 
					A.limit,
					B.weight  
					FROM ch_products_copy
					 AS A
					LEFT JOIN ch_product_detail_copy AS B ON B.pid = A.pid
					LEFT JOIN ch_product_image AS C ON C.gid = A.image_id
					WHERE A.pid IN (".$proId.")";

		$proRes = $this->query($sql);

		$weightTotal = $selectTotal = $selectCount = $priceZHTotal = $priceJPTotal= 0;

		foreach ($proRes as $k => $v) {
			if (!in_array($v['pid'], $cartIdArr) && isset($cartId[$v['pid']])){
				unset($cartId[$v['pid']]);
				continue;
			}

			$v['total'] = (string)$cartId[$v['pid']]['total'];
			$v['select'] = (string)$cartId[$v['pid']]['select'];

			$cartId[$v['pid']]['select'] == 1 ? $selectCount++ : '';
			$cartId[$v['pid']]['select'] == 1 ? $selectTotal += $cartId[$v['pid']]['total'] : '';
			
			// 单品数量金额
			if ($v['total'] > 1) {
				$v['price_zh'] = get_price($v['price_zh'] * $v['total']);
				$v['price_jp'] = get_price($v['price_jp'] * $v['total']);
			}

			// 累加选中金额
			if ($v['select'] == 1) {
				$priceZHTotal += $v['price_zh'];
				$priceJPTotal += $v['price_jp'];

				$weightTotal += $v['weight'] * $v['total'];
			}

			// 允许加减商品数量
			$v['minus'] = '1';
			$v['plus'] = '1';

			// 是否能加减商品数量
			if ($v['rest'] > 0) {
				if ($v['total'] >= $v['limit'] && $v['limit'] != 0) $v['plus'] = '0';
			}else{
				$v['plus'] = '0';
			}

			if ($v['total'] <= 1) $v['minus'] = '0';

			$cartId[$v['pid']] = $v;
		}

		$selectAll = count($cartIdArr) == $selectCount ? 1 : 0;

		if (!is_array($cartId) || count($cartId) <= 0) return $this->_cartInfo();

		$proData = array(
				'list'           => array_values($cartId),
				'select_all'     => (string)$selectAll,
				'select_count'   => (string)$selectTotal,
				'price_zh_total' => (string)get_price($priceZHTotal),
				'price_jp_total' => (string)get_price($priceJPTotal),
				'weight_total'   => (string)$weightTotal,
			);

		return $this->_cartInfo($proData);
	}

	/**
	 * 更新购物车状态
	 * @param array $reqData 请求数据
	 */
	public function setCart($reqData = array()){

		$queryRes = $this->table(tname('user_buylist'))->where(array('user_id' => $reqData['ssid']))->find();

		$cartArr = unserialize($queryRes['cart']);
		$cartIdArr = array_keys($cartArr);

		if (!in_array($reqData['pid'], $cartIdArr) && !in_array($reqData['type'], array(3, 4))) return $this->getCart($reqData['ssid']);
		

		switch ($reqData['type']) {
			// 取消选中
			case '0':
				$cartArr[$reqData['pid']]['select'] = 0;
				break;
			// 选中
			case '1':
				$cartArr[$reqData['pid']]['select'] = 1;
				break;
			// 删除
			case '2':
				$tmpCart = $cartArr[$reqData['pid']];
				unset($cartArr[$reqData['pid']]);
				break;
			// 全选
			case '3':
				foreach ($cartArr as $k => $v) {
					$cartArr[$k]['select'] = 1;
				}
				break;
			// 取消全选
			case '4':
				foreach ($cartArr as $k => $v) {
					$cartArr[$k]['select'] = 0;
				}
				break;
			// 删减商品数量
			case '5':
				if ($cartArr[$reqData['pid']]['total'] > 1){
					$this->upProToCart($reqData['pid'], '2');
					$cartArr[$reqData['pid']]['total']--;
				}
				break;
			// 增加商品数量
			case '6':
				if ($this->checkProRest($reqData['pid'])) {
					$this->upProToCart($reqData['pid'], '1');
					$cartArr[$reqData['pid']]['total']++;
				}
				break;
			default:
				break;
		}

		$cartArr = count($cartArr) <= 0 ? "" : serialize($cartArr);

		$queryRes = $this->query("UPDATE ".tname('user_buylist')." SET cart = '".$cartArr."' WHERE `user_id` = '".$reqData['ssid']."'");
		
		if (isset($tmpCart)) $this->query("UPDATE ".tname('products_copy')." SET rest = rest + ".(int)$tmpCart['total']." WHERE pid = ".(int)$reqData['pid']);

		return $this->getCart($reqData['ssid']);
	}

	/**
	 * 检测商品库存
	 * @param string $pid 商品id
	 */
	public function checkProRest($pid = false){
		$where = array(
				'pid' => $pid,
			);

		$rest = $this->table(tname('products_copy'))->where($where)->count();

		return (int)$rest >= 1 ? true : false;
	}

	/**
	 * 更新商品库存
	 * @param string $pid 商品id
	 */
	public function upProToCart($pid = false, $type = 0){
		
		$sql = "UPDATE ".tname('products_copy')." SET %s WHERE pid = ".(int)$pid;

		switch ($type) {
			case '1':
				$update = " rest = rest - 1 ";
				break;
			case '2':
				$update = " rest = rest + 1 ";
				break;
			default:
				break;
		}

		if (!isset($update)) return;

		$this->query(sprintf($sql, $update));
	}

	/**
	 * 添加到购物车
	 * @param array $proData 商品数组
	 */
	public function addCart($proData = array()){
		$where = array(
				'user_id' => $proData['ssid'],
			);

		$wherePro = array(
				'pid' => $proData['pid'],
			);

		$queryRes = $this->table(tname('user_buylist'))->where($where)->find();

		$proRes = $this->table(tname('products_copy'))->where($wherePro)->find();

		if (!$queryRes['user_id']) $this->creatBuyList($proData['ssid']);

		$userCart = unserialize($queryRes['cart']);

		// 判断产品是否存在
		if ($proRes['pid'] != $proData['pid']) return L('TEXT_ADD_CART_FAILD');

		// 判断限购
		if ($proRes['limit'] > 0) {
			if (isset($userCart[$proData['pid']]) && $userCart[$proData['pid']]['total'] + 1 > $proRes['limit']) return L('TEXT_BUY_PRO_LIMIT');
		}

		$userCart[$proData['pid']]['total']++;

		if (!isset($userCart[$proData['pid']]['select'])) $userCart[$proData['pid']]['select'] = 1;
		$proRes['rest']--;
		// 判断库存
		if ($proRes['rest'] < 0) return L('TEXT_NOT_STOCK');

		$this->query("UPDATE `ch_products_copy` SET rest = '".$proRes['rest']."' WHERE `pid` = '".$proData['pid']."' ");

		$addRes = $this->query("UPDATE `ch_user_buylist` SET cart = '".serialize($userCart)."' WHERE ( `user_id` = '".$proData['ssid']."' )");

		return count($addRes) <= 0 ? true : L('TEXT_ADD_CART_FAILD');
	}

	/**
	 * 添加商品到扫货清单
	 * @param array $proData 商品数组
	 */
	public function addBuyList($proData) {
		$where = array(
				'user_id' => $proData['ssid'],
			);

		$queryRes = $this->table(tname('user_buylist'))->where($where)->find();

		if (!$queryRes['user_id']) {
			$this->creatBuyList($proData['ssid']);
		}

		$userProID = json_decode($queryRes['product_id'], true);

		if (!is_array($userProID)) {
			$userProID = array();
		}

		// array_push($userProID, $proData['pid']);
		if ($proData['type'] == 1) {
			if (in_array($proData['pid'], array_keys($userProID)) ) return L('ERR_EXISTS_PRODUCTID');
			$userProID[$proData['pid']] = 1;
		}else{
			unset($userProID[$proData['pid']]);
		}

		if (count($userProID) <= 0) {
			return true;
		}

		// $queryRes = $this->table(tname('user_buylist'))->where($where)->save('product_id = '.json_encode($userProID));
		$queryRes = $this->query("UPDATE `ch_user_buylist` SET product_id = '".json_encode($userProID)."' WHERE ( `user_id` = '".$proData['ssid']."' )");

		$queryRes = array(
					'has_wantbuy' => isset($userProID[$proData['pid']]) ? '1' : '0',
				);

		return $queryRes;
	}

	/**
	 * 检测是否加入购物清单
	 * @param array $reqData 请求数据
	 */
	public function hasWantBuy($reqData = array()){
		$where = array(
				'user_id' => $reqData['ssid'],
			);

		$queryRes = $this->table(tname('user_buylist'))->where($where)->find();

		$proData = json_decode($queryRes['product_id'], true);

		return isset($proData[$reqData['pid']]) ? '1' : '0';
	}

	/**
	 * 更新扫货清单选中状态
	 * @param array $reqData 请求数据
	 */
	public function setBuyList($reqData = array()){

		$queryRes = $this->table(tname('user_buylist'))->where(array('user_id' => $reqData['ssid']))->find();

		$pidArr = json_decode($queryRes['product_id'], true);

		switch ($reqData['type']) {
			// 取消选中
			case '0':
				if ( in_array($reqData['pid'], array_keys($pidArr)) ) $pidArr[$reqData['pid']] = 0;
				break;
			// 选中
			case '1':
				if ( in_array($reqData['pid'], array_keys($pidArr)) ) $pidArr[$reqData['pid']] = 1;
				break;
			// 删除
			case '2':
				if ( in_array($reqData['pid'], array_keys($pidArr)) ) unset($pidArr[$reqData['pid']]);
				break;
			// 全选
			case '3':
				foreach ($pidArr as $k => $v) {
					$pidArr[$k] = 1;
				}
				break;
			// 取消全选
			case '4':
				foreach ($pidArr as $k => $v) {
					$pidArr[$k] = 0;
				}
				break;
			default:
				break;
		}

		$queryRes = $this->query("UPDATE `ch_user_buylist` SET product_id = '".json_encode($pidArr)."' WHERE ( `user_id` = '".$reqData['ssid']."' )");

		return $this->getBuyList($reqData['ssid']);
	}

	/**
	 * 添加我想去
	 * @param array $reqData 请求数据内容
	 */
	public function addWantGo($reqData = array()){
		$where = array(
				'user_id' => $reqData['ssid'],
			);

		$queryRes = $this->table(tname('user_buylist'))->where($where)->find();

		if (!$queryRes['user_id']) {
			$this->creatBuyList($reqData['ssid']);
		}

		$salerID = json_decode($queryRes['saler_id'], true);

		if (!is_array($salerID)) {
			$salerID = array();
		}

		if (in_array($reqData['sid'], $salerID)) {
			return L('ERR_EXISTS_SALERID');
		}

		array_push($salerID, $reqData['sid']);

		if (count($salerID) <= 0) {
			return true;
		}

		// $queryRes = $this->table(tname('user_buylist'))->where($where)->save('product_id = '.json_encode($userProID));
		$queryRes = $this->query("UPDATE `ch_user_buylist` SET saler_id = '".json_encode($salerID)."' WHERE ( `user_id` = '".$reqData['ssid']."' )");

		return $queryRes;
	}

	/**
	 * 设置 我想去
	 * @param array $reqData 请求数据
	 */
	public function setWantGo($reqData = array()){
		$where = array(
				'user_id' => $reqData['ssid'],
			);

		$queryRes = $this->table(tname('user_buylist'))->where($where)->find();

		$salerID = json_decode($queryRes['saler_id'], true);

		$key = array_search($reqData['sid'], $salerID);
		
		if ($key !== false) array_splice($salerID, $key, 1);

		if (count($salerID) <= 0) $salerID = array();

		$queryRes = $this->query("UPDATE `ch_user_buylist` SET saler_id = '".json_encode($salerID)."' WHERE ( `user_id` = '".$reqData['ssid']."' )");

		return $queryRes;
	}

	/**
	 * 获取扫货清单
	 * @param string $userId 用户id
	 */
	public function getBuyList($userId = false){

		if (!$userId) {
			return false;
		}

		$where = array(
				'user_id' => $userId,
			);

		$productID = $this->table(tname('user_buylist'))->where($where)->getField('product_id');
		
		$productIDArr = array();

		$buyList = json_decode($productID, true);

		$buyListId = array_keys($buyList);

		foreach ($buyListId as $k => $v) {
			if (empty($v) || !$v) {
				continue;
			}

			array_push($productIDArr, $v);
		}

		$productID = implode(',', $productIDArr);

		$sql = "SELECT 
					a.pid,
					b.title_zh,
					b.title_jp,
					b.price_zh,
					b.price_jp,
					CONCAT('".C('API_WEBSITE')."', c.path) AS path,
					CONCAT('".C('API_WEBSITE')."', REPLACE(c.path, '.', '_100_100.')) AS thumb 
				FROM ch_products_copy AS a 
				LEFT JOIN ch_product_detail_copy AS b ON b.pid = a.pid
				LEFT JOIN ch_product_image AS c ON c.gid = a.image_id
				WHERE a.pid IN (".$productID.")";
		$queryRes = $this->query($sql);

		$productList = array();
		$selectCount = 0;

		foreach ($queryRes as $k => $v) {
			$v['select'] = (string)$buyList[$v['pid']];

			if ($v['select'] == 1) {
				$selectCount++;
				$priceJP += $v['price_jp'];
				$priceZH += $v['price_zh'];
			}

			$productList[] = $v;
		}

		$returnRes = array(
				'list' => is_array($productList) ? $productList : array(),
				'selectCount' => (string)$selectCount,
				'price_jp_total' => $priceJP ? get_price($priceJP) : '0.00',
				'price_zh_total' => $priceZH ? get_price($priceZH) : '0.00',
				'selectAll' => count($queryRes) == $selectCount ? '1' : '0',
			);

		return $returnRes;
	}

	/**
	 * 获取 我想去 列表
	 * @param string $userId 用户id
	 */
	public function getWantList($userId = false){
		$salerID = $this->table(tname('user_buylist'))->where(array('user_id' => $userId))->find();

		$salerID = json_decode($salerID['saler_id'], true);

		if (count($salerID) < 0) return false;

		$queryRes = $this->table(tname('saler'))->where(array('saler_id' => array('IN', $salerID)))->order('area DESC')->select();

		$returnRes = array();
		
		foreach ($queryRes as $k => $v) {
			// $v['avg_rating'] = intval(17 * $v['avg_rating']);
			$v['is_gnav'] = $v['gnav_id'] ? "1" : "0";
			$v['avg_rating'] = (string)(10*$v['avg_rating']);
			$returnRes[] = $v;
		}

		return $returnRes;
	}

    /**
     * 迁移 游客数据至注册用户
     * @param string $oldSSID 游客ssid
     * @param string $newSSID 注册用户ssid
     */
    public function mergeUserInfo($oldSSID = false, $newSSID = false){
        
        if ($oldSSID == $newSSID) return true;

        $where = array(
        		'user_id' => $oldSSID,
        	);
        
        $queryRes = $this->table(tname('user_buylist'))->where($where)->find();

        if (count($queryRes) <= 0) return false;
        
        $where['user_id'] = $newSSID;

        $regUserRes = $this->table(tname('user_buylist'))->where($where)->find();

        $wantBuy = json_decode($queryRes['product_id'], true);
        $wantGo = json_decode($queryRes['saler_id'], true);
        $cart = unserialize($queryRes['cart']);

		$regWantBuy = json_decode($regUserRes['product_id'], true);
        // 我想买
        if (is_array($wantBuy) && count($wantBuy) > 0) {
        	foreach ($wantBuy as $k => $v) {
				if (!in_array($k, array_keys($regWantBuy))) {
					$regWantBuy[$k] = $v;
				}
        	}
        }
		
		$regWantGo = json_decode($regUserRes['saler_id'], true);
        // 我想去
        if (is_array($wantGo) && count($wantGo) > 0) {
        	foreach ($wantGo as $k => $v) {
        		if (!in_array($v, $regWantGo)) {
        			array_push($regWantGo, $v);
        		}
        	}
        }
        
        $regCart = unserialize($regUserRes['cart']);
        // 购物车
        if (is_array($cart) && count($cart) > 0) {
        	foreach ($cart as $k => $v) {
        		if (!in_array($k, array_keys($regCart))) {
        			$regCart[$k] = $v;
        		}else{
        			$regCart[$k]['total'] += $v['total'];
        		}
        	}
        }

        $regWantBuy = json_encode($regWantBuy);
        $regWantGo = json_encode($regWantGo);
        $regCart = serialize($regCart);

        $sql = "UPDATE ".tname('user_buylist')." SET product_id = '".$regWantBuy."', saler_id = '".$regWantGo."', cart = '".$regCart."' ";


		$result = $this->query($sql);

		return $result;
    }

	/**
	 * 创建扫货清单
	 * @param string $userId 用户id
	 */
	private function creatBuyList($userId = false){
		$add = array(
				'user_id' => $userId,
			);
		$this->table(tname('user_buylist'))->add($add);
	}

	/**
	 * 返回购物车信息
	 * @param array $proData 商品数据
	 */
	private function _cartInfo($proData = array()){

		$cartInfo = array(
				'list'           => array(),
				'select_count'   => '0',
				'select_all'     => '0',
				'price_zh_total' => '0.00',
				'price_jp_total' => '0.00',
			);

		if (count($proData) <= 0) return $cartInfo;

		return $proData;
	}

	/**
	 * 获取默认收货地址
	 * @param string $userId 用户id
	 */
	private function _getDefaultAddress($userId = false, $aid = 0){

		$where = array(
				'user_id' => $userId,
				// 'default' => 1,
				'status'  => 1,
			);

		if ($aid != 0) {
			$where['id'] = $aid;
			$hasAdd = $this->table(tname('user_address'))->where($where)->count();
			if ($hasAdd < 1) {
				unset($where['id']);
				$where['default'] = 1;
			}
		}else{
			$where['default'] = 1;
		}

		$queryRes = $this->table(tname('user_address'))
						 ->field('id, name, CONCAT(address, "\n", mobile) AS address, mobile')
						 ->where($where)
						 ->find();

		return count($queryRes) <= 0 ? array() : $queryRes;
	}

	/**
	 * 获取购物车总金额
	 * @param string $userId 用户id
	 */
	private function _getCartPrice($userId = false){
		
		$cartInfo = $this->getCart($userId);

		return $cartInfo;
	}

	/**
	 * 获取运费
	 * @param int $weight 商品总重量
	 * @param int $ship 物流选择 
	 */
	private function _getShipping($weight = 0, $ship = 1){

		$ship = (int)$ship <= 0 ? 1 : $ship;

		$sql = "SELECT 
					B.id,
					A.weight, 
					FORMAT((A.shipping_jpy * ".C('JPY')."), 2) AS shipping_zh, 
					B.name, 
					B.ship_day, 
					B.note, 
					IF(B.id = ".$ship.", '1', '0') AS selected
				FROM ch_shipping AS A 
				LEFT JOIN ch_shipping_type AS B ON B.id = A.type
				WHERE 
					A.weight >= ".(int)$weight." 
					AND 
					B.status = 1 
				GROUP BY B.id 
				ORDER BY A.weight, B.id ASC";

		$queryRes = $this->query($sql);

		foreach ($queryRes as $k => $v) {
			if ($v['selected'] == 1) $shipPrice = $v['shipping_zh'];
		}

		$returnRes = array(
				'list'       => $queryRes,
				'ship_price' => $shipPrice,
			);

		return $returnRes;
	}

	/**
	 * 设置服务税
	 * @param double $price 商品总价
	 */
	private function _setPriceTax($price = 0){
		return $price * (1 + C('SERVICE_TAX'));
	}
}
