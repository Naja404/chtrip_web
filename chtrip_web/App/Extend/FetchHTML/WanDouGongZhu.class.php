<?php
/**
* 豌豆公主爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class WanDouGongZhu {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取商品图片信息
	 *
	 */
	public function getShop(){

		$images = array();

		foreach (pq('#slider')->find('li') as $k => $v) {
			$images[] = pq($v)->find('img')->attr('src');
		}

		return $images;
	}
}

?>
