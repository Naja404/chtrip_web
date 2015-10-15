<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class AdminUserModel extends Model{
	
	/**
	 * 检测登录
	 * @param array $data username,password
	 * @return mixed
	 */
	public function checkLogin($data = array()){
		if (count($data) <= 0) {
			return L('error_login');
		}

		$where = array(
				'name'     => $data['username'],
				'password' => md5($data['password']),
			);

		$res = $this->where($where)->find();

		if (!$res['uid']) {
			return L('error_login');
		}

		cookie('uid', $res['uid']);
		cookie('admin_group', $res['group_id']);
		cookie('admin_user', $res['name']);
		cookie('admin_email', $res['email']);

		return true;
	}

}
