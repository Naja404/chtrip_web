<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class ProductModel extends Model{
	
	const DB_MCHA = 'ch_mcha';
	
	/**
	 * 更新店铺信息
	 * @param int $sid 店铺id
	 * @param array $update 更新数据
	 */
	public function upSalerInfo($sid = false, $update = array()){
		$where = array(
				'saler_id' => $sid,
			);
		
		return $this->table(tname('saler'))->where($where)->save($update);
	}

	/**
	 * 获取所有城市列表
	 */
	public function getAllCityList(){
		$sql = "SELECT a.name, IF(a.image_id > 0, CONCAT('http://api.nijigo.com', b.path), '') AS pic_url 
					FROM ch_saler_city AS a 
					LEFT JOIN ch_product_image AS b ON b.gid = a.image_id ";

		$queryRes = $this->query($sql);

		return $queryRes;
	}

	/**
	 * 获取城市列表
	 */
	public function getCityList(){
		$queryRes = $this->table(tname('saler'))->where("area != ''")->group('area')->select();

		$cityList = array();

		foreach ($queryRes as $k => $v) {
			if (!in_array($v['area'], $cityList)) {
				$cityList[] = $v['area'];
			}
		}

		return $cityList;
	}

	/**
	 * 获取产品总量
	 *
	 */
	public function getProductCount($queryData = array()){
		
		$tag = $this->_formatTag($queryData['tag']);
		$subQueryTag = "";

		if ($tag) {
			$subQueryTag = " AND (SELECT COUNT(*) FROM ".tname('products_copy')." WHERE (".$tag.") > 0)";
		}

		$where = "a.status = 1 ".$subQueryTag.$queryData['where'];
		
		$join = array(
				tname('product_detail_copy').' AS b ON b.pid = a.pid',
			);

		return $this->table(tname('products_copy')." AS a")->join($join)->where($where)->count();
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
		

		$where = "a.status = 1 ".$subQueryTag.$queryData['where'];
		
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
					b.summary_zh,
					b.price_zh, 
				  CONCAT('".C('API_WEBSITE')."', REPLACE(c.path, '.', '_100_100.')) AS thumb,
				  a.recommend, a.rest ";

		$order = ' a.sort DESC, a.recommend DESC, a.created DESC ';

		$queryRes = $this->table(tname('products_copy').' AS a')
							->field($field)
							->where($where)
							->join($join)
							->limit($queryData['page'])
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
					b.summary_zh, 
					b.price_zh,
					b.origin_price_zh,  
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
				  a.recommend,
				  c.gid ";

		$queryRes = $this->table(tname('products_copy').' AS a')
							->field($field)
							->where($where)
							->join($join)
							->find();

		if (!is_array($queryRes)) {
			return array();
		}

		$imagesPath = $this->table(tname('product_image'))->field('path')->where(array('parent_id' => $queryRes['gid']))->select();

		$path[] = $queryRes['path'];

		foreach ($imagesPath as $k => $v) {
			$path[] = C('API_WEBSITE').$v['path'];
		}

		$queryRes['path'] = $path;

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
	 * 获取商家总数
	 * @param array $queryData 查询条件数组
	 */
	public function getShopCount($queryData = array()){
		return $this->table(tname('saler'))->where($queryData['where'])->count();
	}

	/**
	 * 获取商家列表
	 * @param array $queryData 查询条件数组
	 */
	public function getShopList($queryData = array()){
		return $this->table(tname('saler'))->field('saler_id, name, pic_url, avg_price, avg_rating, category, area, address, lat, lng')->where($queryData['where'])->order('created DESC')->limit($queryData['page'])->select();
	}

	/**
	 * 获取商家详情
	 * @param int $sid 商家id
	 */
	public function getShopDetail($sid = 0){
		return $this->table(tname('saler'))->where(array('saler_id' => (int)$sid))->find();
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

	/**
	 * 获取专辑详细内容
	 * @param int $aid 专辑id
	 */
	public function getAlbumDetail($aid = 0, $controller = false){
		$sql = "SELECT a.*, b.name AS type_name, c.path  FROM ch_album AS a
						LEFT JOIN ch_album_type AS b ON b.id = a.type
						LEFT JOIN ch_product_image AS c ON c.gid = a.gid
						WHERE a.id = %s LIMIT 1";
		$queryRes = $this->query(sprintf($sql, $aid));

		$queryRes[0]['content'] = htmlspecialchars_decode($queryRes[0]['content']);
		
		return $queryRes[0];
	}

	/**
	 * 获取mcha详细内容
	 * @param int $id 数据id
	 */
	public function getMchaDetail($id = 0){

		$where = array(
				'id' => $id,
			);

		$queryRes = $this->table(self::DB_MCHA)->where($where)->find();

		$queryRes['content'] = htmlspecialchars_decode($queryRes['description']);
		$queryRes['create_time'] = $queryRes['pub_date'];
		unset($queryRes['description']);

		return $queryRes;

	}

	/**
	 * 获取专辑总数
	 * @param array $queryData 查询条件
	 */
	public function getAlbumCount($queryData = array()){
		return $this->table(tname('album'))->where($queryData['where'])->count();
	}

	/**
	 * 获取专辑列表
	 * @param array $queryData 查询条件
	 */
	 public function getAlbumList($queryData = array()) {

	 	$sql = "SELECT a.id AS aid, a.title, a.title_btn, a.address_title, b.name AS type_name, c.path, (a.end_time - UNIX_TIMESTAMP(NOW()) ) AS activityTime  FROM ch_album AS a
						LEFT JOIN ch_album_type AS b ON b.id = a.type
						LEFT JOIN ch_product_image AS c ON c.gid = a.gid WHERE a.status = 1 ORDER BY a.sort DESC, a.recommend DESC LIMIT %s";
	 	
	 	$queryRes = $this->query(sprintf($sql, $queryData['page']));

	 	return $queryRes;
	 }
}
