<?php
/**
* 小桃酱爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class XiaoTaoJiang {

	public $htmlRes;
	
	public function __construct($filePath){
		
		$this->htmlRes = $filePath;

		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取商品图片信息
	 *
	 */
	public function getShop(){

		$data = json_decode($this->htmlRes, true);

		$image = json_decode($data['data']['picture_urls'], true);

		return isset($image) && count($image) > 0 ? $image : array();
	}
}

?>
