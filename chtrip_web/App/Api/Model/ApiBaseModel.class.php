<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class ApiBaseModel extends Model{

	/**
	 * 检测uuid是否绑定过用户
	 * @param string $uuid 设备标识
	 */
	public function hasUserId($uuid  = false){
		$where = array(
				'uuid'   => $uuid,
				'status' => 1,
			);

		$queryRes = $this->table(tname('user'))->where($where)->getField('user_id');

		return $queryRes;
	}

	/**
	 * 创建用户
	 * @param string $userId 用户id
	 * @param string $uuid 设备标识
	 */	
	public function creratUser($userId = false, $uuid = false){
		$insertData = array(
				'user_id'         => $userId,
				'uuid'            => $uuid,
				'status'          => 1,
				'created'         => NOW_TIME,
				'last_login_time' => NOW_TIME,
			);

		return $this->table(tname('user'))->add($insertData);
	}

}
