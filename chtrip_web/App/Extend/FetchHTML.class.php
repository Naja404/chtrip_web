<?php

if(!defined('FETCH_HTML_ROOT')){
	define('FETCH_HTML_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR.'FetchHTML'.DIRECTORY_SEPARATOR);
}

/**
* 
*/
class FetchHTML {

	// 类名
	public $className;
	
	public function __construct($className) {

		$this->className = $className;
		
		require_once FETCH_HTML_ROOT.$className.'.class.php';
	}

	/**
	 * 爬去网页内容
	 * @param string $filePath 文件路径
	 */
	public function fetch($filePath = false, $method = false){
		$classObj = new $this->className($filePath);
		return $classObj->$method();
	}

}

?>
