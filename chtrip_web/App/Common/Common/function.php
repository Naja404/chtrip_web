<?php
/**
 * @author hisoka.2pac@gmail.com
 * @created 2014-9-18
 */

/**
 * 保留2位小数，不四舍五入
 * @param float $num 
 */
function get_price($num){
	return sprintf("%.2f",substr(sprintf("%.4f", $num), 0, -2));
}

/**
 * 获取域名
 * @param string $url 
 */
function get_domain($url = false){
	if (!$url) return false;

	$url = parse_url($url);

	return str_replace('.', '_', $url['host']);
}

/**
 * 隐藏手机号中间几位
 * @param int $mobile 手机号
 */
function hide_mobile($mobile = false){
	
	if (!$mobile) return false;

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
 * 裁剪图片
 * @param string $path 图片路径
 */
function slice_image($path = false){
	$imageInfo = getimagesize($path);

	$orgWidth = $imageInfo[0];
	$orgHeight = $imageInfo[1];

	$src_x = intval(($orgWidth - $orgHeight) / 2);

	$imgObj = imagecreatefromjpeg($path);

	$sliceImg = imagecreatetruecolor($orgHeight, $orgHeight);

	imagecopy($sliceImg, $imgObj, 0, 0, $src_x, 0, $orgHeight, $orgHeight);

	return $sliceImg;
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

/**
 * google 反查地理坐标
 * @param string $address 地址
 */
function google_geo($address = false){

	$url = sprintf(C('GOOGLE_CONF.GEO_URL'), urlencode($address), C('GOOGLE_CONF.GEO_KEY'));

	$json = json_decode(file_get_contents($url), true);

	if ($json['status'] != 'OK') return false;

	return $json['results'][0]['geometry']['location'];

}

/**
 * google 生成地图图片
 * @param string $address 地址
 * @param array $conf 配置信息
 * @param string $filePath 图片路径
 */
function google_static_image($address = false, $conf = array(), $filePath = false){
	
	if (!$filePath) $filePath = C('MAP_IMAGE').mkUUID().'.png';

	if (count($conf) <= 0) $conf = C('GOOGLE_CONF.STATIC_IMAGE_CONF');

	$url = sprintf(C('GOOGLE_CONF.STATIC_IMAGE_URL'), urlencode($address), $conf['scale'], $conf['color'], $conf['latlng'], $conf['zoom'], $conf['size'], C('GOOGLE_CONF.STATIC_IMAGE_KEY'), $conf['language']);

	downloadImage($url, $filePath);

	if (!file_exists($filePath)) return false;

	return '/'.$filePath;
}

/**
 * curl 请求
 * @param string $url 请求地址
 * @param mixed $reqData 请求内容
 */
function put_curl($url = false, $reqData){
	$curl = curl_init();

	curl_setopt($curl,CURLOPT_URL, $url);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,TRUE);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,2);//严格校验
	//设置header
	curl_setopt($curl, CURLOPT_HEADER, FALSE);
	//要求结果为字符串且输出到屏幕上
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $reqData);

	$respData = curl_exec($curl);
	curl_close($curl);

	return $respData;
}

/**
 * 设置微信sign
 * @param array $reqData 请求数组
 */
function set_wx_sign($reqData = array()){
	// $reqData = set_ascii_sort($reqData);
	$str = urldecode(http_build_query($reqData))."&key=4B0F17F38B688EE55855ABF16348E6E1";

	return strtoupper(md5($str));
}

/**
 * 设置ascii 排序
 * @param array $arr 参数数组
 */
function set_ascii_sort($arr = array()){
	
	$key = array_keys($arr);

	foreach ($key as $k => $v) {
		$strArr = str_split($v);
		$tmpV = 0;
		foreach ($strArr as $j => $m) {
			$tmpV += ord($m);
		}
		// $tmp[$v] = $tmpV;
		$tmp[$tmpV] = $v;
	}
	// todo 排序

	$vSort = array_keys($tmp);
	$vCount = count($vSort);

	for ($i=0; $i < $vCount - 1; $i++) { 
		for ($j=0; $j < $vCount - $i - 1; $j++) { 
			if ($vSort[$j] > $vSort[$j+1]) {
				$temp = $vSort[$j];
				$vSort[$j] = $vSort[$j+1];
				$vSort[$j+1] = $temp;
			}
		}
	}

	foreach ($vSort as $k) {

		$tmpArr[$tmp[$k]] = $arr[$tmp[$k]];
	}

	return $tmpArr;
}

/**
 * 格式化成xml
 * @param array $data 数据数组
 */
function format_xml($data = array()){

	$xmlH = "<xml>%s</xml>";
	$xmlB = "<%s>%s</%s>";

	$body = '';

	foreach ($data as $k => $v) {
		$body .= sprintf($xmlB, $k, $v, $k);
	}

	return sprintf($xmlH, $body);
}

/**
 * xml转array
 * @param string $xml 
 */
function xml_to_arr($xml){	
	if(!$xml) throw new WxPayException("xml数据异常！");
	//将XML转为array
	libxml_disable_entity_loader(true);
	
	$arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
	
	return $arr;
}

/**
 * 记录日志
 * @param array $arr mixed
 * @param string $path 日志存储路径
 */
function write_log($arr = array(), $level = 'INFO', $path = false){

	$path  = $path ? $path : C('LOG_PATH').date('y_m_d').'_custom.log';

	Think\Log::write(var_export($arr, true), $level, '', $path);
}

/**
 * 支付宝生成sign
 * @param array $param 请求参数
 * @param array $conf 配置信息
 */
function alipay_sign($param = array(), $conf = array()){
	
	$para_filter = paraFilter($param);

	$para_sort = $para_filter;

	if ($conf['payType'] == 'WAP') $para_sort = argSort($para_filter);
	
	$prestr = createLinkstring($para_sort);

	$sign = rsaSign($prestr, $conf['private_key_path']);

	if ($conf['payType'] == 'APP') $sign = urlencode($sign);

	$para_sort['sign'] = $sign;
	$para_sort['sign_type'] = 'RSA';

	return $para_sort;

}

/**
 * 除去数组中的空值和签名参数
 * @param $para 签名参数组
 * return 去掉空值与签名参数后的新签名参数组
 */
function paraFilter($para) {
	$para_filter = array();
	while (list ($key, $val) = each ($para)) {
		if($key == "sign" || $key == "sign_type" || $val == "")continue;
		else	$para_filter[$key] = $para[$key];
	}
	return $para_filter;
}
/**
 * 对数组排序
 * @param $para 排序前的数组
 * return 排序后的数组
 */
function argSort($para) {
	ksort($para);
	reset($para);
	return $para;
}

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".$val."&";
	}
	//去掉最后一个&字符
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}

/**
 * RSA签名
 * @param $data 待签名数据
 * @param $private_key_path 商户私钥文件路径
 * return 签名结果
 */
function rsaSign($data, $private_key_path) {
    $priKey = file_get_contents($private_key_path);
    $res = openssl_get_privatekey($priKey);
    openssl_sign($data, $sign, $res);
    openssl_free_key($res);
	//base64编码
    $sign = base64_encode($sign);
    return $sign;
}

/**
 * RSA验签
 * @param $data 待签名数据
 * @param $ali_public_key_path 支付宝的公钥文件路径
 * @param $sign 要校对的的签名结果
 * return 验证结果
 */
function rsaVerify($data, $ali_public_key_path, $sign)  {
	$pubKey = file_get_contents($ali_public_key_path);
    $res = openssl_get_publickey($pubKey);
    $result = (bool)openssl_verify($data, base64_decode($sign), $res);
    openssl_free_key($res);    
    return $result;
}

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstringUrlencode($para, $hasStr = false) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		if ($hasStr) {
			$val = str_replace('"', '', $val);
			$arg.=$key."=\"".$val."\"&";
		}else{
			$arg.=$key."=".urlencode($val)."&";
		}
	}
	//去掉最后一个&字符
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}
?>
