<?php
/**
 * @author hisoka.2pac@gmail.com
 * @created 2014-9-18
 */

/**
 * 隐藏手机号中间几位
 * @param int $mobile 手机号
 */
function hide_mobile($mobile = false){
	return substr_replace($mobile, '****', 3, 5);
}

/**
 * 验证手机号
 * @param int $mobile
 */
function check_mobile($mobile = false){

    if (!is_numeric($mobile)) {
        return false;
    }

    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

/**
 * 验证密码
 * @param string $passwd 
 */
function check_pwd($passwd = false){
	
	$len = strlen($passwd);

	if ($len < 6 || $len > 12) return false;

	return true;
}

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
	// header('userId', $this->userId);
	// header('UUID', $this->uuid);

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
function cache($name, $value = '', $options = array()){

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

		return $cache->set($name, $value, $expire);
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

	if (!$size) return C('API_WEBSITE').$path;

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

/**
 * 发邮件
 * @param string $to 收信地址
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件
 * @param string $cc 抄送地址
 */
function send_mail($to,$subject, $body, $attachment = null,$cc = null){
	$config = C('THINK_EMAIL');
	vendor('PHPMailer.class#phpmailer');//导入PHPMailer类

	$mail             = new PHPMailer(true); //PHPMailer对象
	$mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP();  // 设定使用SMTP服务
	$mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能   0 close 、1 errors and messages 、2 messages only
	$mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能

	if ($config['SMTP_PORT'] == 465)
		$mail->SMTPSecure = 'ssl';                 // 使用安全协议

	$mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
	$mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
	$mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
	$mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码

	$mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);

	$replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
	$replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];

	$mail->AddReplyTo($replyEmail, $replyName);
	$mail->FromName   = $replyName;
	$mail->Subject    = $subject;
	$mail->WordWrap   = 80;
	$mail->MsgHTML($body);

	// 判断收件人是否为数组,是数组则遍历添加 Hisoka 2014-7-30
	if (is_array($to)) {
		foreach ($to as $k => $v) {
			$mail->AddAddress($v);
		}
	}else{
		$mail->AddAddress($to);
	}

	//添加抄送
	if($cc){
		if(is_array($cc)){
			foreach ($cc as $_cc){
				$mail->AddCC($_cc);
			}
		}
	}

	if(is_array($attachment)){ // 添加附件
		foreach ($attachment as $file){
			is_file($file) && $mail->AddAttachment($file);
		}
	}else{
		if(file_exists($attachment)){
			$mail->AddAttachment($attachment);
		}
	}
	try{
		$mail->Send();
	}catch (Exception $e){
		return $e->getMessage();
	}
}

/**
 * limit 计算
 * @param int $page 当前页
 * @param int $count 页面条数
 */
function page($page = 1, $count = 25){
	$page = $page <= 0 ? 1 : $page;
	return ($page - 1)*$count.','.$count;
}

/**
 * 写入缓存文件	
 * @param string $content 写入文件内容
 * @param string $path 写入文件路径
 */
function writeFile($content = false, $path = false){
	
	if (!$path) $path = C('FILE_TMP_PATH').md5(time().microtime());
	
	Think\Log::writeFile($content, '', $path);

	return $path;
}

/**
 * 下载图片
 * @param string $url 图片路径
 * @param string $fileName 图片存储路径
 */
function downloadImage($url, $fileName){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0); 
    curl_setopt($ch,CURLOPT_URL,$url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $file_content = curl_exec($ch);
    curl_close($ch);

    $downloaded_file = fopen($fileName, 'w');
    fwrite($downloaded_file, $file_content);
    fclose($downloaded_file);
}

/**
 * 缩放图片尺寸
 */
function resizeImg($path = false, $width = 90, $height = 66, $isCut = false){
	
	$imageInfo = getimagesize($path);
	echo '<pre>';
	print_r($imageInfo);exit();
	$ratio = $imageInfo[1] / $imageInfo[0];

	$newHeight = intval($width / $ratio);
	$newWidth = intval($height / $ratio);

	$imgObj = imagecreatefromjpeg($path);

    if(function_exists("imagecopyresampled")){
       $newImgObj = imagecreatetruecolor($newWidth, $height);
       imagecopyresampled($newImgObj, $imgObj, 0, 0, 0, 0, $newWidth, $height, $imageInfo[0], $imageInfo[1]);
    }else{
       $newImgObj = imagecreate($newWidth, $height);
       imagecopyresized($newImgObj, $imgObj, 0, 0, 0, 0, $newWidth, $height, $imageInfo[0], $imageInfo[1]);
    }

    if ($newHeight != $height && $isCut) {
    	$cropImg = imagecreatetruecolor($newWidth, $height);
    	imagecopy($cropImg, $newImgObj, 0, 0, 0, 0, $newWidth, $height);

    	$newImgObj = $cropImg;
    }

    return $newImgObj;

}

/**
 * 生成uuid
 */
function mkUUID(){
	return md5(NOW_TIME.rand(1000, 9999));
}



/**
 * 显示专辑类型
 * @param int $type 专辑类型
 */
function show_album_type($type = 0){
	

}
?>
