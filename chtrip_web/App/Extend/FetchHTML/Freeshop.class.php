<?php
/**
* Freeshop爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Freeshop {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取物流详情
	 */
	public function getInfo(){

		$info = array();

		foreach (pq('.tbl_result')->find('tr') as $k => $v) {
			
			$tmpInfo = $link = array();

			foreach (pq($v)->find('td') as $j => $m) {

				if ($j != 0) {
					$category = pq($m)->text();
					$category = trim(str_replace($_SERVER["argv"][4]." /", "", $category[0]));
					$tmpInfo['category'] = str_replace(' ', '', $this->_pregTrim($category));
					continue;
				}

				foreach (pq($m)->find('a') as $i => $l) {
					$link[] = pq($l)->attr('href');
				}

				$address = pq($m)->find('span')->text();

				$other = pq($m)->text();
				
				$tmp = $other[0];

				foreach ($address as $a => $b) {
					$tmp = str_replace(trim($b), "", $tmp);
				}

				$tel = $this->_pregTel($tmp);
				$credit = $this->_pregCredit($tmp);

				$tmp = str_replace($tel, "", $tmp);
				$tmp = str_replace($credit, "", $tmp);
				$tmp = str_replace("[MAP]", "", $tmp);

				$address[] = $this->_pregTrim($tmp);

				$tmpInfo = array(
						'address' => $this->_fetchTrim($address),
						'tel'     => $this->_pregTrim($tel),
						'credit'  => $this->_pregTrim($credit, 1),
						'link'    => $link,
					);
			}

			$info[] = $tmpInfo;
		}

		return $info;
	}

	/**
	 * 匹配电话
	 * @param string $cotent 字符串
	 */
	private function _pregTel($content = false){

		preg_match_all('/Tel:.*\s/', $content, $match);

		if (isset($match[0][0])) return $match[0][0];

		return 'false';
	}

	/**
	 * 匹配信用卡
	 * @param string $content 字符串
	 */
	private function _pregCredit($content = false){

		preg_match_all('/Credit Card:\s+.*/', $content, $match);

		if (isset($match[0][0])) return $match[0][0];

		return 'false';
	}

	/**
	 * 去空操作
	 * @param string $content 字符串
	 */
	private function _pregTrim($content = false, $isTrimS = false){
		// return str_replace("/\s+|\S+/", "", $content);

		$content = str_replace(PHP_EOL, '', $content);

		if ($isTrimS) {
			$content = str_replace("Credit Card:", "", $content);
			return str_replace(" ", "", trim($content));
		}

		return preg_replace("/(\r\n|\n|\r|\t)/i", "", trim($content));
	}

	/**
	 * 遍历处理换行
	 * @param array $data 待处理数据
	 */
	private function _fetchTrim($data = array()){
		
		$newData = array();

		foreach ($data as $k => $v) {
			$newData[] = str_replace(PHP_EOL, '', $v);
		}

		return $newData;
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

	/**
	 * 获取url
	 */
	public function getGallery(){
		
		$info = array();

		foreach (pq('.listdiv')->find('li') as $k => $v) {
			$info[] = pq($v)->find('a')->attr('href');
		}

		return $info;
	}

	/**
	 * 获取图片url
	 */
	public function getImageUrl(){
		$info = array();

		foreach (pq('#hgallery')->find('img') as $k => $v) {
			$info[] = pq($v)->attr('src');
		}

		return $info;
	}

	/**
	 * 获取rss图片
	 */
	public function getRssImage(){

		$imageArr = pq()->find('img')->attr('src');

		return $imageArr;
	}

}

?>
