<?php
/**
* 麦芽城爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class MaiyaMall {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取商品图片信息
	 *
	 */
	public function getShop(){
		
		$images = array();

		foreach (pq(".bd.p-slider-viewport")->find('li') as $k => $v) {
			$images[] = pq($v)->find('img')->attr('src');
		}

		return $images;
	}
}

?>
