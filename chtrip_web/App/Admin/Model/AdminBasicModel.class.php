<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class AdminBasicModel extends Model{

	/**
	 * 获取对应权限菜单 
	 * 缓存以分组id区分
	 */
	public function getMenu(){
		if (!cookie('uid') || !cookie('admin_user')) {
			$this->error(L('error_auth'), U('Login/login'));
		}

		if (cache(C('CACHE.ADMIN_LEFT_MENU').cookie('admin_group'))) {
			return cache(C('CACHE.ADMIN_LEFT_MENU').cookie('admin_group'));
		}

		$sql = "SELECT a.rules FROM ".tname('admin_auth_group')." AS a 
					LEFT JOIN ".tname('admin_auth_group_access')." AS b 
						ON b.uid = '".cookie('uid')."' 
					WHERE a.id = b.group_id ";

		$result = $this->table(tname('admin_auth_rule'))->where('FIND_IN_SET(id, ('.$sql.')) AND status = 1 AND is_display = 1')->order('id, menu_module')->select();

		$menuArr = array();

		foreach ($result as $k => $v) {

			if (!in_array($v['menu_module'], array_keys($menuArr))) {
				$menuArr[$v['menu_module']] = array(
						'title' => $v['menu_title'],
						'module' => $v['menu_module'],
					);
			}

			$menuArr[$v['menu_module']]['list'][] = array(
						'url' => str_replace('Admin/', '', $v['name']),
						'title' => $v['title'],
				);
		}

		cache(C('CACHE.ADMIN_LEFT_MENU').cookie('admin_group'), $menuArr, 3600);

		return $menuArr;
	}

	/**
	 * 设置用户操作日志
	 * 
	 */
	public function setUserLog($options = array()){

		$insertData = array(
					'user_id'       => cookie('uid'),
					'user_group'    => cookie('admin_group'),
					'action_ip'     => $_SERVER['REMOTE_ADDR'],
					'action_url'    => $_SERVER['REQUEST_URI'],
					'action_menu'   => MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,
					'action_status' => $options['status'] ? $options['status'] : 0,
					'event_id'		=> $options['event_id'] ? $options['event_id'] : '',
					'log_time'      => NOW_TIME,
			);

		$this->table(tname('admin_action_log'))->add($insertData);
	}

	/**
	 * 获取头部配置信息
	 *
	 */
	public function getHeaderList(){
		if (cache(C('CACHE.ADMIN_HEADER'))) {
			return cache(C('CACHE.ADMIN_HEADER'));
		}

		// $queryRes = $this->table(tname('admin_auth_rule'))->field('menu_module')->group('menu_module')->select();

		$querySql = "SELECT 
						GROUP_CONCAT(name) AS list 
						FROM (
							SELECT 
								menu_module AS name 
							FROM ".tname('admin_auth_rule')." 
							GROUP BY menu_module
							) AS tmp";

		$queryRes = $this->query($querySql);

		if (empty($queryRes[0]['list'])) {
			return false;
		}

		cache(C('CACHE.ADMIN_HEADER'), $queryRes[0]['list']);

		return $queryRes[0]['list'];

	}
}
