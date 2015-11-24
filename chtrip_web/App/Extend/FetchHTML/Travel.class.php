<?php
/**
* 景点爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Travel {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	public function getTest(){
		$what = pq()->find('title')->text();

		echo '<pre>';
		print_r($what);exit;
	}

	/**
	 * 获取图片url
	 *
	 */
	public function getImgUrl(){

		$img = array();

		foreach (pq('.photo-list2.price-list')->find('li') as $k => $v) {
			$img[] = pq($v)->find('img')->attr('src');
		}

		return $img;
	}

	/**
	 * 获取店铺url
	 *
	 */
	public function getShopUrl(){

		foreach (pq()->find('li') as $k => $v) {
			$url = pq($v)->find('a')->attr('href');

			if (!empty($url)) return $url; 
		}

		return false;
	}

	/**
	 * 获取景点详细url
	 *
	 */
	public function getTravelUrl(){
		
		$urlArr = array();
		
		foreach (pq()->find('a') as $k => $v) {
			$url = pq($v)->attr('href');

			if (in_array($url, $urlArr)) continue;

			$urlArr[] = $url;
		}

		return $urlArr;
	}

}

?>
