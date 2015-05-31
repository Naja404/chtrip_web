<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UtilModel extends Model{

	/**
	 * 设置token内容
	 * @param array $setData 数据内容
	 */
	public function setToken($setData = array()){
			$data = array(
				'user_id'         => md5(),
				'uuid'            => md5(),
				'status'          => 1,
				'created'         => NOW_TIME,
				'last_login_time' => NOW_TIME,
				'token'           => $setData['token'],
			);

		return $this->table(tname('user'))->add($data);
	}

}
