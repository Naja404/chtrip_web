<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UserModel extends Model{

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

		if (in_array($proData['pid'], $userProID)) {
			return L('ERR_EXISTS_PRODUCTID');
		}

		array_push($userProID, $proData['pid']);

		if (count($userProID) <= 0) {
			return true;
		}

		// $queryRes = $this->table(tname('user_buylist'))->where($where)->save('product_id = '.json_encode($userProID));
		$queryRes = $this->query("UPDATE `ch_user_buylist` SET product_id = '".json_encode($userProID)."' WHERE ( `user_id` = '".$proData['ssid']."' )");

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

		foreach (json_decode($productID, true) as $k => $v) {
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

		foreach ($queryRes as $k => $v) {
			$priceJP += $v['price_jp'];
			$priceZH += $v['price_zh'];
		}

		$returnRes = array(
				'list' => is_array($queryRes) ? $queryRes : array(),
				'price_jp_total' => $priceJP ? $priceJP : '0.00',
				'price_zh_total' => $priceZH ? $priceZH : '0.00',
			);

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

}
