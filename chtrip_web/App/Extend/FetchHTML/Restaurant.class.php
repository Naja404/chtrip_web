<?php
/**
* 餐厅爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Restaurant {

	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath);
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
	 * 获取详细内容
	 *
	 */
	public function getDetail(){

		$detail = array();

		$avg = pq('.price')->text();

		$detail['avg'] = $avg[0] ? str_replace('¥', '', $avg[0]) : '';

		$star = pq('.pic-txt')->find('p')->html();

		preg_match('/star-(\d+)/', $star, $starNum);

		$detail['star'] = $starNum[1] ? $starNum[1] / 10 : '';

		// 分类
		foreach (pq('.shop-crumbs')->find('a') as $k => $v) {

			$text = pq($v)->text();

			if (!empty($text[0])) {
				$detail['category'][] = $text[0];
			}
		}

		if (count($detail['category']) >= 3) {
			$detail['category_name'] = $detail['category'][2];
		}

		$detail['category'] = json_encode($detail['category']);

		$recText = pq('.comm-new-tag')->find('span')->text();

		$comment = pq('.modebox.shop-comment')->find('span')->text();

		foreach ($recText as $k => $v) {
			$recommend[] = trim($v);
		}

		$detail['recommend'] = json_encode($recommend);

		if (!empty($comment[0])) {
			preg_match('/(\d+)/', $comment[0], $comment);

			if (is_numeric($comment[0])) $detail['pop'] = $comment[0];
		}

		return $detail;
	}

	/**
	 * 获取图片url
	 *
	 */
	public function getRestaurantImg(){

		$img = array();

		foreach (pq('.picture-list')->find('li') as $k => $v) {
			$img[] = pq($v)->find('img')->attr('src');
		}

		return $img;
	}

	/**
	 * 获取图片url other
	 *
	 */
	public function getRestaurantImgOther(){

		$img = array();

		foreach (pq('')->find('li') as $k => $v) {

			$tmp = pq($v)->find('img')->attr('src');

			if (!empty($tmp)) {
				$img[] = $tmp;
			}
		}

		return $img;
	}

	/**
	 * 获取餐厅详细页url
	 *
	 */
	public function getRestaurantUrl(){

		$url = array();

		foreach (pq()->find('li') as $k => $v) {
			$url[] = pq($v)->find('a')->attr('href');
		}

		return $url;
	}

	/**
	 * 获取餐厅详细内容
	 *
	 */
	public function getRestaurant(){

		foreach (pq('.details-mode.info-address')->find('a') as $k => $v) {
			$arr[] = pq($v)->text();
		}

		$name = pq('.shop-name')->text();

		$image = pq('.pic-txt')->find('img')->attr('src');

		if (!$image) $image = pq('.new_pic')->find('img')->attr('src');

		$address = preg_replace('/\s+/', '', $arr[0][0]);

		$detail = array(
				'name'    => trim($name[0]),
				'address' => $address,
				'tel'     => is_numeric(trim($arr[1][0])) ? trim($arr[1][0]) : trim($arr[2][0]),
				'image' => $image,
			);

		return $detail;
	}

}

?>
