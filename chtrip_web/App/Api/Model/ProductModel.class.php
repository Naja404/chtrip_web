<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class ProductModel extends Model{

	/**
	 * 获取产品总量
	 *
	 */
	public function getProductCount($queryData = array()){
		
		$tag = $this->_formatTag($queryData['tag']);
		$subQueryTag = "";

		if ($tag) {
			$subQueryTag = " AND (SELECT COUNT(*) FROM ".tname('products')." WHERE (".$tag.") > 0)";
		}

		$where = $where = "a.status = 1 ".$subQueryTag;

		return $this->table(tname('products')." AS a")->where($where)->count();
	}

	/**
	 * 获取产品列表
	 * @param array $queryData 查询内容
	 */
	public function getProductList($queryData = array()){

		$tag = $this->_formatTag($queryData['tag']);
		$subQueryTag = "";

		if ($tag) {
			$subQueryTag = " AND (SELECT COUNT(*) FROM ".tname('products_copy')." WHERE (".$tag.") > 0)";
		}
		

		$where = "a.status = 1 ".$subQueryTag;
		
		$join = array(
				tname('product_detail_copy').' AS b ON b.pid = a.pid',
				tname('product_image').' AS c ON c.gid = a.image_id',
				tname('product_shipping').' AS d ON d.id = b.shipping_type',
				// tname('product_tag').' AS e ON e.tid IN (a.tag)',
				tname('saler').' AS f ON f.saler_id = a.saler_id',
			);

		$queryTag = "SELECT GROUP_CONCAT(name) FROM ".tname('product_tag')." WHERE FIND_IN_SET(tid, a.tag) ";
		$queryImage = "SELECT GROUP_CONCAT(CONCAT('".C('API_WEBSITE')."', path)) FROM ".tname('product_image')." WHERE FIND_IN_SET(parent_id, a.image_id) OR gid = a.image_id";


		$field = "a.pid, 
					b.title_zh, 
					b.title_jp, 
					b.price_zh, 
					b.price_jp,
					b.buy_url, 
					b.description_zh,
					b.description_jp,
				  (".$queryImage.") AS path,
				  CONCAT('".C('API_WEBSITE')."', REPLACE(c.path, '.', '_100_100.')) AS thumb,
				  d.name AS shipping_name, 
				  (".$queryTag.") AS tag_name, 
				  f.name AS sale_name, 
				  f.sale_url, 
				  a.created, 
				  a.sort, 
				  a.recommend ";

		$order = ' a.sort DESC, a.recommend DESC, a.created DESC ';

		$queryRes = $this->table(tname('products_copy').' AS a')
							->field($field)
							->where($where)
							->join($join)
							->page($queryData['page'])
							->order($order)
							->select();

		return $queryRes;
	}

	/**
	 * 根据pid获取详细内容
	 * @param int $pid 产品id
	 */
	public function getProductDetail($pid = false){

		if (intval($pid) === 0) {
			return array();
		}

		$where = array(
				'a.pid' => $pid,
				'a.status' => 1,
			);
		
		$join = array(
				tname('product_detail_copy').' AS b ON b.pid = a.pid',
				tname('product_image').' AS c ON c.gid = a.image_id',
				tname('product_shipping').' AS d ON d.id = b.shipping_type',
				// tname('product_tag').' AS e ON e.tid IN (a.tag)',
				tname('saler').' AS f ON f.saler_id = a.saler_id',
			);

		$queryTag = "SELECT GROUP_CONCAT(name) FROM ".tname('product_tag')." WHERE FIND_IN_SET(tid, a.tag) ";


		$field = "a.pid, 
					b.title_zh, 
					b.title_jp, 
					b.price_zh, 
					b.price_jp,
					b.buy_url, 
					b.description_zh,
					b.description_jp,
				  CONCAT('".C('API_WEBSITE')."', c.path) AS path,
				  CONCAT('".C('API_WEBSITE')."', REPLACE(c.path, '.', '_100_100.')) AS thumb,
				  d.name AS shipping_name, 
				  (".$queryTag.") AS tag_name, 
				  f.name AS sale_name, 
				  f.sale_url, 
				  a.created, 
				  a.sort, 
				  a.recommend ";

		$queryRes = $this->table(tname('products_copy').' AS a')
							->field($field)
							->where($where)
							->join($join)
							->find();

		if (!is_array($queryRes)) {
			return array();
		}

		return $queryRes;
	}

	/**
	 * 根据gid获取更多图片
	 * @param int $gid
	 */
	public function getIMGByGid($gid = false){
		
		if (intval($gid) === 0) {
			return array();
		}

		$where = array(
				'parent_id' => $gid,
			);

		$queryRes = $this->table(tname('product_image'))
				->field(array('path'))
				->where($where)
				->select();

		return $queryRes;
	}

	/**
	 * 格式化tag到sql
	 * @param array $tag tag数组
	 */
	private function _formatTag($tag = array()){

		if (strlen(implode('', $tag)) <= 0) {
			return false;
		}

		if (!is_array($tag) || count($tag) <= 0) {
			return false;
		}

		$tagArr = array();

		foreach ($tag as $k => $v) {
			$tagArr[] = "FIND_IN_SET(".$v.", a.tag)";
		}

		$tagRes = implode(' OR ', $tagArr);

		return $tagRes;
	}

}
