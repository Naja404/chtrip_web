<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class SettingModel extends Model{

	// 定义返回信息
	public $result = array('status' => 0, 'msg' => 'ERROR PARAM');

	// 后台菜单验证
	protected $_validate = array(
			array('menu_module', 'require', '{%error_menu_module_empty}', self::MUST_VALIDATE),
			array('menu_title', 'require', '{%error_menu_module_title_empty}', self::MUST_VALIDATE),
			array('name', 'require', '{%error_menu_name_empty}', self::MUST_VALIDATE),
			array('title', 'require', '{%error_menu_title_empty}', self::MUST_VALIDATE),
		);

	// 用户验证
	public $_userRule = array(
			array('group_id', 'require', '{%error_user_group_id_empty}', self::MUST_VALIDATE),
			array('name', 'require', '{%error_user_name_empty}', self::MUST_VALIDATE),
			array('password', 'require', '{%error_user_password_empty}', self::MUST_VALIDATE),
			array('email', 'require', '{%error_user_email_empty}', self::MUST_VALIDATE),
			array('email', 'email', '{%error_user_email_empty}', self::MUST_VALIDATE),
		);

	// 用户组验证
	public $_groupRule = array(
			array('module', 'require', '{%error_group_module_empty}', self::MUST_VALIDATE),
			array('title', 'require', '{%error_group_title_empty}', self::MUST_VALIDATE),
		);

	/**
	 * 获取菜单列表
	 * @param array $data 查询参数
	 */
	public function getMenus($data = array()){
		$field = $data['field'];
		$where = $data['where'] ? $data['where'] : '';
		$order = $data['order'] ? $data['order'] : '';
		$page  = $data['page'] ? $data['page'] : '';
		if ($page) {
			$list  = $this->table(tname('admin_auth_rule'))->field($field)->where($where)->order($order)->page($page)->select();
		}else{
			$list  = $this->table(tname('admin_auth_rule'))->field($field)->where($where)->order($order)->select();
		}

		return $list;
	}

	/**
	 * 菜单表单添加/修改
	 * @param array post数据
	 */
	public function setMenuForm($data = array()){

		if ($this->create($data) === FALSE) {
			return array('status' => 0, 'msg' => $this->getError());
		}

		$data['module'] = 'Admin';

		if (isset($data['id']) && $data['id'] > 0) {
			$status = $this->table(tname('admin_auth_rule'))->save($data);
		}else{
			$status = $this->table(tname('admin_auth_rule'))->add($data);
		}

		if ($status >= 1) {
			$this->result = array(
					'status' => 1,
					'msg' => L('success_add_menu'),
				);
		}

		return $this->result;
	}

	/**
	 * 根据菜单id获取详细内容
	 * @param int $rid 菜单id
	 */
	public function getMenuDetail($rid = 0){

		if ($rid <= 0) {
			return $this->result;
		}
		
		$res = $this->table(tname('admin_auth_rule'))->where(array('id' => $rid))->find();

		if ($res['id'] == $rid) {
			$this->result = array(
					'status' => 1,
					'info' => $res,
				);
		}

		return $this->result;

	}

	/**
	 * 根据id删除菜单
	 * @param int $rid 菜单id
	 */
	public function delMenu($rid){
		if ($rid <= 0) {
			return $this->result;
		}
		
		$res = $this->table(tname('admin_auth_rule'))->where(array('id' => $rid))->delete();

		if ($res) {
			$this->result = array(
					'status' => 1,
				);
		}

		return $this->result;	
	}

	/**
	 * 获取用户列表
	 * @param array $data 查询条件
	 */
	public function getUsers($data = array()){
		$sql = "SELECT a.uid, a.group_id, a.name, a.email, b.title, a.created FROM ".tname('admin_user')." AS a 
					LEFT JOIN ".tname('admin_auth_group')." AS b ON b.id = a.group_id 
				ORDER BY a.uid, a.group_id ASC LIMIT ".$data['page'];
		
		$list = $this->query($sql);

		return $list;
	}

	/**
	 * 获取分组列表
	 *
	 */
	public function getGroup(){

		$list = $this->table(tname('admin_auth_group'))->where(array('status' => 1))->select();

		return $list;
	}

	/**
	 * 后台用户 添加/删除 管理
	 * @param array $data 数据内容
	 */
	public function setUserForm($data = array()){

		if (isset($data['uid']) && $data['uid'] > 0) {
			unset($this->_userRule[2]);
		}

		if ($this->validate($this->_userRule)->create($data) === FALSE) {
			return array('status' => 0, 'msg' => $this->getError());
		}

		if (isset($data['password']) && !empty($data['password'])) {
			$data['password'] = md5($data['password']);
		}else{
			unset($data['password']);
		}

		if (isset($data['uid']) && $data['uid'] > 0) {
			$status = $this->table(tname('admin_user'))->where(array('uid' => $data['uid']))->save($data);
			$type = 2;
		}else{
			$data['created'] = NOW_TIME;
			$data['uid'] = $status = $this->table(tname('admin_user'))->add($data);
			$type = 1;
		}

		if ($status >= 1) {
			$this->result = array(
					'status' => 1,
					'msg' => L('success_add_user'),
				);

			$this->_upUserGroup($data['uid'], $data['group_id'], $type);
		}

		return $this->result;
	}

	/**
	 * 更新用户组数据
	 * @param int $uid 用户id
	 * @param int $gid 组id
	 * @param int $type 类型 1.添加 2.修改 3.删除
	 */
	private function _upUserGroup($uid = 0, $gid = 0, $type = false){
		$table = tname('admin_auth_group_access');
		switch ($type) {
			case 1:
				$this->table($table)->add(array('uid' => $uid, 'group_id' => $gid));
				break;
			case 2:
				$this->table($table)->where(array('uid' => $uid))->save(array('group_id' => $gid));
				break;
			case 3:
				$this->table($table)->where(array('uid' => $uid))->delete();
				break;
			default:
				break;
		}
	}

	/**
	 * 根据uid获取用户详细内容
	 * @param int $uid 用户id
	 */
	public function getUserDetail($uid = 0){
		if ($uid <= 0) {
			return $this->result;
		}
		
		$res = $this->table(tname('admin_user'))->where(array('uid' => $uid))->find();

		if ($res['uid'] == $uid) {
			$this->result = array(
					'status' => 1,
					'info' => $res,
				);
		}

		return $this->result;
	}

	/**
	 * 根据uid删除用户
	 * @param int $uid 用户id
	 */
	public function delUser($uid = 0){
		if ($uid <= 0) {
			return $this->result;
		}
		
		$res = $this->table(tname('admin_user'))->where(array('uid' => $uid))->delete();

		if ($res) {
			$this->result = array(
					'status' => 1,
				);
			$this->_upUserGroup($uid, false, 3);
		}

		return $this->result;
	}

	/**
	 * 用户组管理列表
	 *
	 */
	public function getGroups($data = array()){
		$field = '';
		$order = $data['order'] ? $data['order'] : '';
		$page  = $data['page'] ? $data['page'] : '';
		$list  = $this->table(tname('admin_auth_group'))->field($field)->where($where)->order($order)->page($page)->select();

		return $list;
	}

	/**
	 * 设置用户组 添加/修改
	 * @param array $data 数据内容
	 */
	public function setGroupForm($data = array()){

		if ($this->validate($this->_groupRule)->create($data) === FALSE) {
			return array('status' => 0, 'msg' => $this->getError());
		}

		$data['rules'] = implode(',', $data['rules']);

		if (isset($data['id']) && $data['id'] > 0) {
			$status = $this->table(tname('admin_auth_group'))->where(array('id' => $data['id']))->save($data);
		}else{
			unset($data['id']);
			$status = $this->table(tname('admin_auth_group'))->add($data);
		}

		if ($status >= 1) {
			$this->result = array(
					'status' => 1,
					'msg' => L('success_add_group'),
				);
		}

		return $this->result;
	}

	/**
	 * 根据id获取用户组内容
	 * @param int $rid 用户组id
	 */	
	public function getGroupDetail($rid = 0){
		if ($rid <= 0) {
			return $this->result;
		}
		
		$res = $this->table(tname('admin_auth_group'))->where(array('id' => $rid))->find();

		if ($res['id'] == $rid) {
			$res['rules'] = explode(',', $res['rules']);
			$this->result = array(
					'status' => 1,
					'info' => $res,
				);
		}

		return $this->result;
	}
	
	/**
	 * 根据id删除菜单
	 * @param int $gid 用户组id
	 */
	public function delGroup($gid = 0){
		if ($gid <= 0) {
			return $this->result;
		}
		
		$res = $this->table(tname('admin_auth_group'))->where(array('id' => $gid))->delete();

		if ($res) {
			$this->result = array(
					'status' => 1,
				);
		}

		return $this->result;	
	}
}
