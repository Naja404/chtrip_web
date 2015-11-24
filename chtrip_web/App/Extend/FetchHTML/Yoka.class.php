<?php
/**
* yoka爬虫类
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Yoka {
	
	public function __construct($filePath){
		phpQuery::newDocumentFile($filePath); 
	}

	public function getDetail(){

		foreach (pq('.p_r')->find('dl') as $k => $v) {
			$dt = pq($v)->find('dt')->text();

			$dd = pq($v)->find('dd')->text();

			foreach ($dd as $j => $m) {
				if ($dt[$j] == '产品介绍：') {
					$jieshao = pq($v)->find('.p_r_det.int')->text();
					$m = trim(str_replace('收起', '', $jieshao[0]));
				}

				if ($dt[$j] == '使用说明：') {
					$shuoming = pq($v)->find('.p_r_det.exp')->text();
					$m = trim(str_replace('收起', '', $shuoming[0]));
				}

				$return[] = array(
						'name' => trim($dt[$j]),
						'value' => trim($m),
					);
			}

		}

		$return = $this->formatDetail($return);

		foreach (pq('.p_l_img')->find('li') as $j => $m) {
			$return['image'][] = pq($m)->find('img')->attr('src');
		}

		$return['image'] = json_encode($return['image']);

		return $return;
	}

	/**
	 * 格式化详情内容
	 * @param array $detail 数据内容
	 */
	public function formatDetail($detail = array()){

		$field = array(
				'产品品类：'  => 'category',
				'品牌：'     => 'brand',
				'所属系列：'  => 'series',
				'参考价格：'  => 'price',
				'质地：'     => 'texture',
				'适合肤质：'  => 'skin',
				'是否防晒：'  => 'sunscreen',
				'是否颗粒：'  => 'granule',
				'是否透明：'  => 'transform',
				'是否带喷嘴：' => 'injector',
				'产品介绍：'  => 'introduce',
				'使用说明：'  => 'instruction',
			);

		$return = array();

		foreach ($detail as $k => $v) {
			$return[$field[$v['name']]] = $v['value'];
		}
		return $return;
	}

}

?>
