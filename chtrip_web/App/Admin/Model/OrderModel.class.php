<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class OrderModel extends Model{

	/**
	 * 获取订单总数
	 */
	public function getOrderTotal(){
		return $this->table(tname('order'))->count();
	}

	/**
	 * 获取订单列表
	 * @param array $reqData 请求内容
	 */
	public function getOrderList($reqData = array()){

		$jsonAddress = tname('user_address')." AS B ON B.id = A.address_id";

		$queryList = $this->table(tname('order').' AS A')
						 ->field('A.*, B.name, B.mobile, B.address')
						 ->join($jsonAddress)
						 ->limit($reqData['page'])
						 ->order($reqData['order'])
						 ->select();

		return $queryList;
	}

	/**
	 * 获取订单详情
	 * @param int $oid 订单id
	 */
	public function getOrderDetail($oid = 0){

		$where = array(
				'A.oid' => $oid,
			);

		$joinAddress = tname('user_address')." AS B ON B.id = A.address_id";

		$orderInfo = $this->table(tname('order').' AS A')
							 ->field('A.*, B.name, B.mobile, B.address')
							 ->join($joinAddress)
							 ->where($where)
							 ->find();

		if ($orderInfo['oid'] != $oid) return false;

		$whereDetail = array(
				'oid' => $oid,
			);

		$joinImage = tname('product_image')." AS B ON B.gid = A.image_id ";

		$orderInfo['list'] = $this->table(tname('order_detail')." AS A ")
								  ->field("A.*, CONCAT('".C('API_WEBSITE')."', REPLACE(B.path, '.', '_100_100.')) AS thumb ")
								  ->join($joinImage)
								  ->where($where)
								  ->select();

		return $orderInfo;
	}
}
