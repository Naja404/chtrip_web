<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class OrderModel extends Model{

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
