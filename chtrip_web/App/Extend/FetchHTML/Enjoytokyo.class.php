<?php
/**
* 京王百货爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Enjoytokyo {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取店铺信息
	 *
	 */
	public function getShop(){
		$title = pq('.title_com01')->text();

		$titleTmp = $contentTmp = array();

		foreach (pq('.tablestyle03')->find('th') as $k => $v) {
			$tmpStr = pq($v)->text();
			$titleTmp[] = array(
							'k' => trim($tmpStr[0]),
					);
		}

		foreach (pq('.tablestyle03')->find('td') as $k => $v) {
			$tmp = pq($v)->text();
			$titleTmp[$k]['v'] = str_replace(array(chr(32), chr(10), chr(13), "	"), "", trim($tmp[0]));
		}

		$returnRes = array(
				'title' => trim($title[0]),
				'data'  => $titleTmp,
			);

		return $returnRes;
	}
}

?>
