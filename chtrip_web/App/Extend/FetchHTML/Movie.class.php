<?php
/**
* 电影院爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Movie {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

	/**
	 * 获取电影详情
	 *
	 */
	public function getMovieDetail(){
		$name = pq('.detail_head_name')->find('h1')->text();
		$rating = pq('.point')->text();
		$movieDetail = array(
				'name'   => $name[0],
				'image'  => pq('.ui_pic.mr30')->find('img')->attr('src'),
				'rating' => $rating[0],
			);

		return $movieDetail;

	}

	/**
	 * 今日上映
	 *
	 */
	public function getTodayMovie(){

		$image = array();

		foreach (pq('.even_center')->find('li') as $k => $v) {

			$name = pq($v)->find('em')->text();

			$movieId = pq($v)->find('a')->attr('id');

			$tmp = array(
					'movieId' => $movieId,
					'image'   => pq($v)->find('img')->attr('src'),
					'name'    => $name[0] ? $name[0] : '',
				);

			array_push($image, $tmp);
		}

		return $image;
	}

	/**
	 * 格瓦拉电影院
	 *
	 */
	public function getGewaraCinema(){

		$cinema = array();

		foreach (pq('.movieList')->find('li') as $k => $v) {
			
			$name = pq($v)->find('h2')->text();
			$address = pq($v)->find('.mt10')->text();

			if (!count($name)) continue;

			$cinema[] = array(
					'name'    => trim($name[0]),
					'address' => $this->_formatGewaraAddress($address[0]),
					'url'     => pq($v)->find('.colorRed')->attr('href'),
					'image'	  => $this->_formatGewaraImg(pq($v)->find('img')->attr('style')),
				);
		}

		return $cinema;
	}

	/**
	 * 格瓦拉电影院详情
	 *
	 */
	public function getGewaraCinemaDetail(){
		$infoKey   = pq('.detail_head_text')->find('dt')->text();
		$infoValue = pq('.detail_head_text')->find('dd')->text();

		$mainArr = $roundArr = $keyArr = array();

		foreach ($infoKey as $k => $v) {

			if (in_array($v, $keyArr)) continue;

			array_push($keyArr, $v);

			$mainArr[] = array(
					'key'   => $v,
					'value' => trim($infoValue[$k]),
				);
		}

		foreach (pq('.trafficn.none')->find('dl') as $k => $v) {
			$roundArr[] = array(
					'key' => pq($v)->find('dt')->text(),
					'value' => $this->_formatCinemaInfo(pq($v)->find('dd')->html()),
				);	
		 }

		 return array('main' => $mainArr, 'round' => $roundArr);

	}

	/**
	 * 格式化电影院信息
	 * @param string $info 电影院周边信息
	 */
	private function _formatCinemaInfo($info = false){
		$arr = explode('<br>', $info);

		foreach ($arr as $k => $v) {
			$arr[$k] = trim(strip_tags($v));

			if (empty($arr[$k])) unset($arr[$k]);
		}

		return $arr;
	}

	/**
	 * 格式化格瓦拉地址信息
	 * @param string $address 地址
	 */
	private function _formatGewaraAddress($address = false){
		$address = trim(str_replace('[交通]', '', $address));

		preg_match('/\[(.*)\]/', $address, $areaName);

		$return = array(
				'address' => trim(str_replace($areaName[0], '', $address)),
				'area'    => $areaName[1],
			);

		return $return;
	}

	/**
	 * 格式化格瓦拉图片地址
	 * @param string $mixStr 混合字符串
	 */
	private function _formatGewaraImg($mixStr = false){

		preg_match('/(?<=\\().*(?=\\))/', $mixStr, $res);

		return $res[0];
	}
}

?>
