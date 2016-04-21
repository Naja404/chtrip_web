<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class OrderModel extends Model{

	/**
	 * 获取订单内容
	 * @param array $reqData 请求内容
	 */
	public function getOrderList($reqData = array()){
		$where = array(
				'A.user_id' => $reqData['ssid'],
			);

		if (isset($reqData['status']) && is_numeric($reqData['status'])) $where['A.status'] = (int)$reqData['status'];

		if ($where['A.status'] == 10) $where['A.status'] = array('in', '0, 1');

		$field = "A.oid, 
				A.ship_fee, 
				A.total_fee, 
				A.created, 
				A.pay_status, 
				A.status, 
				A.comment,
				B.title_zh, 
				CONCAT('￥', B.price_zh) AS price_zh, 
				CONCAT('共', B.quantity,'件') AS quantity, 
				CONCAT('".C('API_WEBSITE')."', REPLACE(C.path, '.', '_100_100.')) AS thumb ";
		$joinDetail = tname('order_detail')." AS B ON B.oid = A.oid ";
		$joinPro = tname('product_image')." AS C ON C.gid = B.image_id ";
		$order = 'A.created DESC';

		$queryRes = $this->field($field)
						 ->table(tname('order').' AS A')
						 ->join($joinDetail)
						 ->join($joinPro)
						 ->where($where)
						 ->order($order)
						 ->select();


		$tmpList = array();

		foreach ($queryRes as $k => $v) {
			$tmpList[$v['oid']][] = $v;
		}

		$orderList = array();

		foreach ($tmpList as $k => $v) {
			
			$tmpArr = array(
					'oid'          => $v[0]['oid'],
					'oid_label'    => sprintf(L('TEXT_ORDER_NUMBER'), $v[0]['oid']),
					'status'       => $v[0]['status'],
					'status_label' => L('TEXT_ORDER_STATUS_'.$v[0]['status']),
					'ship_url'     => sprintf(C('SHIP_URL'), $reqData['ssid'], $v[0]['oid']),
					'total_fee'    => '￥'.$v[0]['total_fee'],
					'created'      => date('Y-m-d H:i:s', time()),
					'has_comment'  => $v[0]['comment'] == 1 ? '1' : '0',
					'comment_url'  => $v[0]['status'] == 1 ? $this->_getCommentUrl($v[0]['oid'], $reqData['ssid']) : '',
					'pro'          => $v,
				);

			$orderList[] = $tmpArr;
		}

		return $orderList;

	}

	/**
	 * 取消订单
	 * @param array $reqData 请求数据
	 */
	public function cancelOrder($reqData = array()){

		$queryRes = $this->getOrderInfo($reqData);

		if ($queryRes['status'] != 4) return L('ERROR_PARAM');


		$res = $this->query("UPDATE `".tname('order')."` SET status = '0', opera = '".$reqData['ssid']."' WHERE ( `user_id` = '".$reqData['ssid']."' AND `oid` = '".$reqData['oid']."')");

		if ($res !== false) {
			
			$where = array(
					'oid' => $reqData['oid'],
				);

			$proArr = $this->table(tname('order_detail'))->field('pid, quantity')->where($where)->select();

			foreach ($proArr as $k => $v) {
				
				$sql = "UPDATE `".tname('products_copy')."` SET rest = rest + ".$v['quantity']." WHERE (`pid` = ".$v['pid'].")";
				
				$this->query($sql);
			}
		}

		return $res !== false ? true : L('ERROR_PARAM');
	}

	/**
	 * 检测用户订单
	 * @param array $reqData 请求数据
	 */
	public function chackUserPay($reqData = array()){
		
		$queryRes = $this->getOrderInfo($reqData);

		if ($queryRes['status'] != 4) return L('ERROR_PARAM');

		return $queryRes;
	}

	/**
	 * 获取订单详情
	 * @param type item
	 */
	public function getOrderInfo($reqData = array()){
		
		$where = array(
				'user_id' => $reqData['ssid'],
				'oid'     => $reqData['oid'],
			);

		$queryRes = $this->where($where)->find();

		return $queryRes;
	}

	/**
	 * 创建订单id
	 */
	public function makeOrderId(){
		
		$maxId = $this->field('id')->order('id DESC')->find();

		$maxId['id']++;

		$idLen = strlen($maxId['id']);
		$strTmp = '';

		if ($idLen < 5) {
			for ($i=0; $i < 5 - $idLen; $i++) { 
				$strTmp .= '0';
			}
		}

		$orderId = date('Ymd', time()).rand(1000, 9999).$strTmp.$maxId['id'];

		return $orderId;
	}

	/**
	 * 更新订单id
	 * @param int $oid 订单id
	 */
	public function upOrderId($oid = 0){
		$midOid = substr($oid, 9, 4);

		$newOid = str_replace($midOid, rand(1000, 9999), $oid);

		if ($newOid == $oid) $newOid = str_replace($midOid, rand(1000, 9999), $oid);

		$sql = "UPDATE `".tname('order')."` SET wx_oid = '".$newOid."' WHERE `oid` = '".$oid."'";

		$this->query($sql);

		return $newOid;
	}

	/**
	 * 获取评价链接
	 * @param int $oid 订单号
	 * @param string $userID 用户id
	 */
	private function _getCommentUrl($oid = false, $userID = false){
		return sprintf(C('PUBLISH_COMMENT_URL'), 1, $oid, $userID);
	}
}
