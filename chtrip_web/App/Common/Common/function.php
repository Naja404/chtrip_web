<?php
/**
 * @author hisoka.2pac@gmail.com
 * @created 2014-9-18
 */


/**
 *
 * 返回带表前缀的表名
 * @param string $tablename 表名
 */
function tname($tablename) {
	return C('DB_PREFIX').$tablename.C('DB_SUFFIX');
}

/**
 * json 输出
 * @param mixed $msg 消息内容
 * @param bool $status 
 */
function json_msg($msg = '',$status = 0){
	
	// 返回JSON数据格式到客户端 包含状态信息
	header('Content-Type:application/json; charset=utf-8');
	header('userId', $this->userId);
	header('UUID', $this->uuid);
	
	$data = array('status' => (string)$status);
	
	if($msg) {
		is_array($msg) ? $data = array_merge($data, array('data' => $msg)) : $data['error'] = (string)$msg;
	}
	
	exit(json_encode($data));
}

/**
 * 缓存,默认Redis缓存
 * @param  mixed $name    缓存名
 * @param  mixed $value   缓存数据
 * @param  mixed $options 缓存参数
 * @return mixed
 */
function cache($name,$value = '',$options = array()){
	
	static $cache   =   '';

	if(empty($cache)){

		$type       =   isset($options['type']) ? $options['type']:'Redis';
		$cache 	    =   Think\Cache::getInstance($type);
	}
	
	//获取缓存
	if($value === ''){
		return $cache->get($name);
	}elseif(is_null($value)){
		//删除缓存
		return $cache->rm($name);
	}else{
		//缓存数据
		if(is_array($options))
			$expire = $options['expire'] ? $options['expire'] : '';
		else
			$expire = is_numeric($options)	?	$options	  : '';

		return $cache->set($name,$value,$expire);
	}
}

/**
 * 删除树形keys下的所有key
 * @param string $keys 树形键值
 * @param array $options 参数数组
 * @return bool
 */
function rm_all_cache($keys = false, $options = array()){
	
	static $all_cache   =   '';

	if(empty($all_cache)){
		$type       =   isset($options['type']) ? $options['type'] : 'Redis';
		$all_cache 	    =   Think\Cache::getInstance($type);
	}

	$keys = $all_cache->keys($keys);

	if (!is_array($keys)) {
		return false;
	}

	foreach ($keys as $k => $v) {
		$all_cache->rm($v);
	}

	return true;
}

/**
 * 显示图片
 * @param string $path 图片路径
 * @param string $size 图片尺寸
 */
function show_image($path = false, $size = false){

	if (!file_exists('.'.$path)) {
		return $path;
	}

	$extension = substr(strrchr($path, '.'), 1);

	$new_path = str_replace('.'.$extension, '_'.$size.'.'.$extension, $path);

	if (!file_exists('.'.$new_path)) {
		return false;
	}

	return C('API_WEBSITE').$new_path;

}

/**
 * url获取get参数
 * @param string $url				URL
 * @param string or array $param	GET参数名
 */
function url_fetch_get_param($url, $param) {
	
	$params = is_array($param) ? $param : array($param);
	$result = array();
	$arr = explode('?', $url);
	$arr2 = explode('&', $arr[1]);
	foreach ($params as $v) {
		foreach ($arr2 as $v2) {
			$len = strlen($v.'=');
			$str = substr($v2, 0, $len);
			if ($str == $v.'=') {
				$result[$v] = substr($v2, $len);
				break;
			}
		}
	}
	
	return !empty($result) ? (is_array($param) ? $result : $result[$param]) : '';
}

/**
 * 生成分页limit
 * @param int $pageNum 页数
 * @param int $pageSize 数据条数
 */
function make_page($pageNum = 0, $pageSize = 0, $returnNum = false){
	if (!$pageSize) {
		$pageSize = C('PAGE_LIMIT');
	}

	if ($returnNum) {
		return $pageNum * $pageSize;
	}

	return (($pageNum - 1) * $pageSize).','.$pageSize;

}

/**
 * 获取header所有信息
 * @param string $void 
 */
function get_header_info($void = false){
	
	$headers = array();

	foreach($_SERVER as $key => $value) {
		if (substr($key, 0, 5) == 'HTTP_') {
			$key = substr($key, 5);
			$key = ucfirst(strtolower($key));
			$headers[$key] = $value;
			continue;
		}
	}

	if ($void) {
		return isset($headers[ucfirst(strtolower($name))]) ? $headers[ucfirst(strtolower($name))] :'';
	}

	return $headers;
}

?> 
