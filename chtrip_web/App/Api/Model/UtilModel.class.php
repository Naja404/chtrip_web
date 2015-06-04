<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UtilModel extends Model{

	/**
	 * 设置token内容
	 * @param string $token 设备数据内容
	 */
	public function setToken($token = false){

			$data = array(
				'user_id'         => md5(NOW_TIME),
				'uuid'            => md5(microtime()),
				'status'          => 1,
				'created'         => NOW_TIME,
				'last_login_time' => NOW_TIME,
				'token'           => $this->_spliceToken(htmlspecialchars_decode($token)),
			);

		$hasUserID = $this->table(tname('user'))->where(array('token' => $data['token']))->getField('user_id');

		if (!empty($hasUserID)) {
			return $hasUserID;
		}

		$queryRes = $this->table(tname('user'))->add($data);

		return $queryRes > 0 ? $data['user_id'] : false;
	}

	/**
	 * 获取用户id
	 * @param string $userID 用户id
	 * @param string $token 设备token
	 */
	public function getSSID($userID = false, $token = false){
		$where = array(
				'user_id' => $userID,
				'token'   => $this->_spliceToken(htmlspecialchars_decode($token)),
			);
		return $this->table(tname('user'))->where($where)->getField('user_id');
	}

	/**
	 * 格式化token为字符串
	 * @param string $token 设备token
	 */
	private function _spliceToken($token = false){
		return preg_replace('/<|>|\s+/', '', $token);
	}
}
