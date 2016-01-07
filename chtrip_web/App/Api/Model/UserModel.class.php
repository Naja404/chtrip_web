<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UserModel extends Model{

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
					B.price_jp 
					FROM ch_products_copy
					 AS A
					LEFT JOIN ch_product_detail_copy AS B ON B.pid = A.pid
					LEFT JOIN ch_product_image AS C ON C.gid = A.image_id
					WHERE A.pid IN (".$proId.")";

		$proRes = $this->query($sql);

		$selectCount = $priceZHTotal = $priceJPTotal= 0;

		foreach ($proRes as $k => $v) {
			if (!in_array($v['pid'], $cartIdArr) && isset($cartId[$v['pid']])){
				unset($cartId[$v['pid']]);
				continue;
			}

			$v['total'] = (string)$cartId[$v['pid']]['total'];
			$v['select'] = (string)$cartId[$v['pid']]['select'];

			$cartId[$v['pid']]['select'] == 1 ? $selectCount++ : '';
			$cartId[$v['pid']] = $v;
			$priceZHTotal += $v['price_zh'];
			$priceJPTotal += $v['price_jp'];
		}

		$selectAll = count($cartIdArr) == $selectCount ? 1 : 0;

		if (!is_array($cartId) || count($cartId) <= 0) return $this->_cartInfo();

		$proData = array(
				'list'           => $cartId,
				'select_all'     => (string)$selectAll,
				'select_count'   => (string)$selectCount,
				'price_zh_total' => (string)sprintf("%1\$.2f", round($priceZHTotal, 2)),
				'price_jp_total' => (string)sprintf("%1\$.2f", round($priceJPTotal, 2)),
			);

		return $this->_cartInfo($proData);
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

		if (in_array($proData['pid'], array_keys($userProID)) ) {
			return L('ERR_EXISTS_PRODUCTID');
		}

		// array_push($userProID, $proData['pid']);
		$userProID[$proData['pid']] = 1;

		if (count($userProID) <= 0) {
			return true;
		}

		// $queryRes = $this->table(tname('user_buylist'))->where($where)->save('product_id = '.json_encode($userProID));
		$queryRes = $this->query("UPDATE `ch_user_buylist` SET product_id = '".json_encode($userProID)."' WHERE ( `user_id` = '".$proData['ssid']."' )");

		return $queryRes;
	}

	/**
	 * 更新扫货清单选中状态
	 * @param array $reqData 请求数据
	 */
	public function setBuyList($reqData = array()){

		$queryRes = $this->table(tname('user_buylist'))->where(array('user_id' => $reqData['ssid']))->find();

		$pidArr = json_decode($queryRes['product_id'], true);

		switch ($reqData['type']) {
			// 删除
			case '0':
				if ( in_array($reqData['pid'], array_keys($pidArr)) ) $pidArr[$reqData['pid']] = 0;
				break;
			// 选中
			case '1':
				if ( in_array($reqData['pid'], array_keys($pidArr)) ) $pidArr[$reqData['pid']] = 1;
				break;
			// 取消选中
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
				'price_jp_total' => $priceJP ? $priceJP : '0.00',
				'price_zh_total' => $priceZH ? $priceZH : '0.00',
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
			$v['avg_rating'] = (string)(10*$v['avg_rating']);
			$returnRes[] = $v;
		}

		return $returnRes;
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
}
