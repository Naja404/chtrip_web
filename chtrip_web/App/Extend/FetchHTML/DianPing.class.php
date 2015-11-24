	<?php
/**
* 大众点评爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class DianPing {
	
	public function __construct($filePath){
		// phpQuery::newDocumentFile($filePath); 
		phpQuery::newDocumentHTML($filePath); 

		$this->shopDetail = array(
				'0'  => 'address',
				'1'  => 'tel',
				'10' => 'rate',
				'11' => 'avg',
			);

		$this->shopTraffic = array(
				'营业时间' => 'open_time',
				'公交信息' => 'traffic',
			);
	}

	/**
	 * 获取分类索引url
	 *
	 */
	public function getCateUrl(){
		
		$url = array();

		foreach (pq('#selectMenu')->find('a') as $k => $v) {
			$url[] = array(
					'name' => pq($v)->text()[0],
					'url'  => pq($v)->attr('href'),
				);
		}

		return $url;
	}

	/**
	 * 获取
	 *
	 */
	public function getShopUrl(){

		$url = array();

		foreach (pq()->find('li') as $k => $v) {
			$url[] = array(
					'name' => trim(pq($v)->find('h3')->text()[0]),
					'shop_url' => pq($v)->find('a')->attr('href'),
				);
		}

		return $url;
	}

	/**
	 * 获取商场内容
	 *
	 */
	public function getMall(){
		$url = array();

		foreach (pq()->find('li') as $k => $v) {
			$url[] = array(
					'name' => trim(pq($v)->find('h3')->text()[0]),
					'mall_url' => pq($v)->find('a')->attr('href'),
				);
		}

		return $url;
	}

	/**
	 * 获取商场分类内容
	 *
	 */
	public function getMallCate(){
		
		$url = pq('.modebox.shop-mall')->find('a')->attr('href');

		return $url;

	}

	/**
	 * 获取商场店铺数据
	 *
	 */
	public function getMallShop(){

		$shop = array();

		foreach (pq()->find('li') as $k => $v) {
			$rate = trim(pq($v)->find('.comment > span')->attr('class'));

			preg_match('/\d+/', $rate, $rateNum);

			$shop[] = array(
					'name'     => trim(pq($v)->find('h3')->text()[0]),
					'avg'      => str_replace('人均:￥', '', trim(pq($v)->find('.comment')->text()[0])),
					'shop_url' => pq($v)->find('a')->attr('href'),
					'floor'	   => trim(pq($v)->find('.intro.Fix > span')->text()[0]),
					'cate'     => trim(pq($v)->find('.type')->text()[0]),
					'rate'     => trim($rateNum[0]) / 10,
				); 
		}

		return $shop;
	}

	/**
	 * 获取店铺详情
	 *
	 */
	public function getShopDetail(){
		$textData = pq('.details-mode.info-address')->find('a')->text();

		$detail = array();
		
		foreach ($textData as $k => $v) {
			$detail[$this->shopDetail[$k]] = preg_replace("/\s/","", trim($v));
		}

		$detail['avg'] = str_replace('¥', '', trim(pq('.pic-txt')->find('p')->text()[0]));

		$avgHTML = pq('.pic-txt')->find('p')->html();

		preg_match('/star-(\d+)/', $avgHTML, $star);

		$detail['rate'] = trim($star[1]) / 10;

		if (empty($detail['address'])) {
			$textData = pq('.shop-details')->find('a')->text();

			$detail = array();
			
			foreach ($textData as $k => $v) {
				$detail[$this->shopDetail[$k]] = preg_replace("/\s/","", trim($v));
			}

			$detail['avg'] = str_replace('¥', '', trim(pq('.pic-txt')->find('p')->text()[0]));

			$avgHTML = pq('.shop-details')->find('figure')->html();

			preg_match('/star-(\d+)/', $avgHTML, $star);

			$detail['rate'] = trim($star[1]) / 10;
		}

		if (empty($detail['address'])) {
			$tmpAdd = pq('.icon-address')->text();
			if ($tmpAdd) $detail['address'] = $tmpAdd;
		}


		return $detail;
	}

	/**
	 * 获取店铺地址内容
	 *
	 */
	public function getShopAddress(){

		$address = array(
				'address' => trim(pq('.add.bottom-border')->text()[0]),
				'tel'     => trim(pq('.tel.bottom-border')->text()[0]),
			);
		return $address;
	}

	/**
	 * 获取店铺更多信息
	 *
	 */
	public function getShopTraffic(){
		
		$detail = array();

		foreach (pq('.busines-list')->find('li') as $k => $v) {
			
			$h6Name = trim(pq($v)->find('h6')->text()[0]);

			$tmpVal = str_replace($h6Name, '', trim(pq($v)->text()[0]));

			$detail[$this->shopTraffic[$h6Name]] = trim($tmpVal);
		}

		return $detail;
	}

	/**
	 * 获取搜索列表内容
	 *
	 */
	public function getSearchList(){
		$url = pq('.item.Fix')->attr('href');

		if (is_array($url)) {
			return $url[0] ? trim($url[0]) : '';
		}

		return $url;
	}

	/**
	 * 获取店铺楼层
	 *
	 */
	public function getShopFloor(){

		foreach (pq('.search-list.J_list')->find('li') as $k => $v) {
			$floor = pq($v)->find('.intro.Fix > span')->text();
			if ($floor[0]) return trim($floor[0]);
		}

		return false;
	}

	/**
	 * 获取shop url
	 *
	 */
	public function getShopFloorUrl(){
		
		foreach (pq('.search-list.J_list')->find('li') as $k => $v) {
			$url = pq($v)->find('.mod-link')->attr('href');

			if ($url) return $url;
		}

		return false;
	}


}

?>
