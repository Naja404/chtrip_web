<?php
/**
* 菠萝蜜爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Bolome {
	
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

		$image = $data['data']['picture_urls'];

		$newImages = array();

		foreach ($image as $k => $v) {
			$newImages[] = sprintf("https://img.bolo.me/%s@!largeJpegLQ", $v);
		}

		return isset($newImages) && count($newImages) > 0 ? $newImages : array();


	}
}

?>
