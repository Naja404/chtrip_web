<?php
/**
 * 数据模型
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class ExcelModel extends Model{
	public $shopCate = array('购物' => 1, '美食' => 2, '酒店' => 3, '景点' => 4);

	/**
	 * 添加商家数据
	 * @param array $data 商家数据
	 */	
	public function insertShopData($data = array()){
		
		if (count($data) <= 0) return false;

		foreach ($data as $k => $v) {
			$v['type']      = (int)$this->shopCate[trim($v['type'])];
			$v['created']   = time();
			$v['status']    = 1;

			if (!empty($v['avg_price'])) $v['avg_price'] = sprintf('￥%s/人', $v['avg_price']);

			$this->table(tname('saler'))->add($v);
		}

		return true;
	}

	/**
	 * 添加商品数据
	 * @param array $data 商品数据
	 */
	public function insertProData($data = array()){

		if (count($data) <= 0) return false;

		foreach ($data as $k => $v) {

			$gid = $this->insertImg($v['image_url']);

			if (!is_numeric($gid)) return false;

			$products = array(
					'tag'      => '',
					'image_id' => $gid,
					'saler_id' => 1,
					'status'   => 1,
					'created'  => time(),
				);

			$pid = $this->table(tname('products_copy'))->add($products);

			if (!is_numeric($pid)) return $false;

			$v['pid'] = $pid;
			if ($v['price_zh']) $v['price_zh'] = floor($v['price_zh'] * 100) / 100;
			if ($v['price_jp']) $v['price_jp'] = floor($v['price_jp'] * 100) / 100;

			$v['created'] = time();

			unset($v['image_url']);
			unset($v['shop_name']);

			$this->table(tname('product_detail_copy'))->add($v);
		}

		return true;

	}

	/**
	 * 设置图片	
	 * @param string $path 路径地址
	 */
	public function insertImg($path = false){

		$add = array(
				'path'    => trim($path),
				'created' => time(),
			);

		return $this->table(tname('product_image'))->add($add);
	}

}
