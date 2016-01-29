<?php
/**
* Ems爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Ems {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取物流详情
	 */
	public function getInfo(){

		$list = $tmp = array();

		foreach (pq('.tableType01.txt_c.m_b5')->find('tr') as $k => $v) {

			foreach (pq($v)->find('td') as $j => $m) {
				
				$thTitle = pq($m)->text();

				$tmp[] = $thTitle[0];
			}

			if (count($tmp) == 5) $list[] = $tmp;

			$tmp = array();
		}

		return $list;
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
