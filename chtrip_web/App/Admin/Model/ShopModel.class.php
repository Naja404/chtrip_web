<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class ShopModel extends Model{

	const DB_SALER = 'ch_saler';

	/**
	 * 获取区域内容
	 */
	public function getArea(){
		return $this->table(self::DB_SALER)->field('area')->where('area != "" ')->group('area')->select();
	}

	/**
	 * 获取分类
	 */
	public function getCategory(){
		return $this->table(self::DB_SALER)->field('category')->where('category !="" ')->group('category')->select();
	}

	/**
	 * 获取商家总数
	 */
	public function getShopCount(){
		return $this->table(tname('saler'))->count();
	}

	/**
	 * 获取商家列表
	 * @param array $data 查询数组
	 */
	public function getShopList($data = array()){
		return $this->table(tname('saler'))->where(array('status' => 1))->page($data['page'])->order('created DESC')->select();
	}

	/**
	 * 删除商家
	 * @param int $id 商户id
	 */
	public function delShop($id = 0){

		return $this->table(tname('saler'))->where(array('saler_id' => (int)$id))->save(array('status' => 0));
	}

	/**
	 * 添加商家
	 * @param string $imagePath 图片路径
	 */
	public function addShop($imagePath = 0){
		$add = array(
				'name'        => I('post.title'),
				'description' => I('post.shopDescription'),
				'sale_url'    => I('post.sale_url'),
				'img_url'     => $imagePath,
				'pic_url'     => C('API_WEBSITE').$imagePath,
				'address'     => I('post.address'),
				'open_time'   => I('post.open_time'),
				'tel'         => I('post.tel'),
				'avg_price'   => I('post.avg_price'),
				'avg_rating'  => I('post.avg_rating'),
				'tag_name'    => I('post.tag_name'),
				'type'        => I('post.type'),
				'category'    => I('post.category'),
				'area'        => I('post.area'),
				'status'      => 1,
				'created'     => NOW_TIME,
		);

		$sid = $this->table(tname('saler'))->add($add);

		return $sid > 0 ? true : false;
	}

}
