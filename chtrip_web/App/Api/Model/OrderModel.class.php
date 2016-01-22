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

		$field = "A.oid, 
				A.ship_fee, 
				A.total_fee, 
				A.created, 
				A.pay_status, 
				A.status, 
				B.title_zh, 
				CONCAT('￥', B.price_zh) AS price_zh, 
				CONCAT('共', B.quantity,'件') AS quantity, 
				CONCAT('".C('API_WEBSITE')."', REPLACE(C.path, '.', '_100_100.')) AS thumb ";
		$joinDetail = tname('order_detail')." AS B ON B.oid = A.oid ";
		$joinPro = tname('product_image')." AS C ON C.gid = B.image_id ";
		$order = 'A.created, A.oid DESC';

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
					'oid'       => sprintf(L('TEXT_ORDER_NUMBER'), $v[0]['oid']),
					'status'    => L('TEXT_ORDER_STATUS_'.$v[0]['status']),
					'total_fee' => '￥'.$v[0]['total_fee'],
					'created'   => date('Y-m-d H:i:s', time()),
					'pro'       => $v,
				);

			$orderList[] = $tmpArr;
		}

		return $orderList;

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
}
