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

		$shipInfo = $this->table(tname('order_ship'))->where(array('oid' => $oid))->find();

		$orderInfo['ship_info'] = unserialize($shipInfo['content']);

		return $orderInfo;
	}

	/**
	 * 检查订单的物流信息正确性
	 * @param array $reqData 请求数据
	 */
	public function checkShipId($reqData = array()){
		
		$where = array(
				'oid' => $reqData['oid'],
				'sid' => $reqData['ship_id'],
			);

		$count = $this->table(tname('order_ship'))->where($where)->count();

		return $count > 0 ? false : true;
	}

	/**
	 * 更新订单物流状态
	 * @param int $oid 订单号
	 * @param int $status 订单状态 0.订单取消 1.订单完成 2.待发货 3.待收货 4.待付款
	 */
	public function upOrderStatus($oid = 0, $status = 0){

		$statuArr = array(
				0, 1, 2, 3, 4
			);

		if (!in_array($status, $statuArr)) return false;

		$sql = "UPDATE ".tname('order')." SET status = '".(int)$status."' WHERE oid = '".$oid."'";

		$this->query($sql);

		return true;
	}
}
