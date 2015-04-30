<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class ProductModel extends Model{

	/**
	 * 获取产品总量
	 *
	 */
	public function getProductCount(){
		return $this->table(tname('products'))->where(array('status' => 1))->count();
	}

	/**
	 * 获取产品列表
	 * @param array $queryData 查询内容
	 */
	public function getProductList($queryData = array()){

		$where = array(
				'a.status' => 1,
			);
		
		$join = array(
				tname('product_detail').' AS b ON b.pid = a.pid',
				tname('product_image').' AS c ON c.gid = a.image_id',
				tname('product_shipping').' AS d ON d.id = b.shipping_type',
				// tname('product_tag').' AS e ON e.tid IN (a.tag)',
				tname('saler').' AS f ON f.saler_id = a.saler_id',
			);

		$queryTag = "SELECT GROUP_CONCAT(name) FROM ".tname('product_tag')." WHERE FIND_IN_SET(tid, a.tag) ";

		$field = 'a.pid, b.title, b.price, b.comments, b.sales, b.views, b.buy_url, c.path, d.name AS shipping_name, ('.$queryTag.') AS tag_name, f.name AS sale_name, f.sale_url, a.created';

		$order = ' a.created DESC ';

		$queryRes = $this->table(tname('products').' AS a')
				->field($field)
				->where($where)
				->join($join)
				->page($queryData['page'])
				->order($order)
				->select();

		return $queryRes;
	}

	/**
	 * 添加产品
	 * @param int $image_id 图片id 
	 */
	public function addProduct($imageId = false){

		$tag = implode(',', I('post.tag'));

		$addData = array(
				'tag'       => strlen($tag) <= 1 ? '' : $tag,
				'sort'      => intval(I('post.sort', 0)),
				'recommend' => I('post.recommend', 0) ? 1 : 0,
				'image_id'  => intval($imageId),
				'saler_id'  => intval(I('post.saler_id', 0)),
				'created'   => NOW_TIME,
			);

		$pid = $this->table(tname('products'))->add($addData);

		if (!$pid) {
			return false;
		}

		$detailID = $this->_addProductDetail($pid);

		if (!$detailID) {
			
			$this->table(tname('products'))->where(array('pid' => $pid))->delete();

			return false;
		}

		$this->_getBuyUrlIMG($imageId, $_REQUEST['buy_url']);

		return $pid;

	}

	/**
	 * 添加图片 
	 * @param string $imgPath
	 */
	public function setIMGId($imgPath = false){

		$addIMG = array(
				'created' => NOW_TIME,
				'path'    => $imgPath,
			);

		$gid = $this->table(tname('product_image'))->add($addIMG);

		if (!$gid) {
			return false;
		}

		return $gid;
	}

	/**
	 * 获取邮费选项
	 *
	 */
	public function getShipType(){

		if (cache(C('CACHE.ADMIN_PRODUCT_SHIPTYPE'))) {
			return cache(C('CACHE.ADMIN_PRODUCT_SHIPTYPE'));
		}

		$queryRes = $this->table(tname('product_shipping'))->field('id,name')->select();

		if (count($queryRes) && is_array($queryRes)) {
			cache(C('CACHE.ADMIN_PRODUCT_SHIPTYPE'), $queryRes);
		}

		return $queryRes;
	}

	/**
	 * 获取店铺列表
	 *
	 */
	public function getSaler(){
		
		if (cache(C('CACHE.ADMIN_PRODUCT_SALE'))) {
			return cache(C('CACHE.ADMIN_PRODUCT_SALE'));
		}

		$where = array(
				'status' => 1,
			);

		$queryRes = $this->table(tname('saler'))->field('saler_id, name')->where($where)->select();

		if (count($queryRes) && is_array($queryRes)) {
			cache(C('CACHE.ADMIN_PRODUCT_SALE'), $queryRes);
		}

		return $queryRes;
	}

	/**
	 * 获取tag列表
	 *
	 */
	public function getTags(){

		if(cache(C('CACHE.ADMIN_PRODUCT_TAG'))){
			return cache(C('CACHE.ADMIN_PRODUCT_TAG'));
		}

		$queryRes = $this->table(tname('product_tag'))->field('tid,name')->select();

		if (count($queryRes) && is_array($queryRes)) {
			cache(C('CACHE.ADMIN_PRODUCT_TAG'), $queryRes);
		}

		return $queryRes;
	}

	/**
	 * 添加产品详细内容
	 * @param int $pid 产品id
	 */
	private function _addProductDetail($pid = false){
		
		if (!$pid) {
			return false;
		}

		$addDetail = array(
				'pid'           => $pid,
				'title'         => trim(I('post.title')),
				'description'   => I('post.description', '', 'htmlspecialchars'),
				'price'         => trim(I('post.price')),
				'shipping_type' => I('post.shipping_type'),
				'comments'      => I('post.comments'),
				'sales'         => I('post.sales'),
				'views'         => I('post.views'),
				'buy_url'       => trim(I('post.buy_url')),
				'created'       => NOW_TIME,
			);

		$detailID = $this->table(tname('product_detail'))->add($addDetail);

		if (!$detailID) {
			return false;
		}

		return $detailID;

	}

	/**
	 * 批量添加url图片
	 * @param int $imageId 父级图片id
	 * @param string $buyUrl 购买链接
	 */
	private function _getBuyUrlIMG($imageId = 0, $buyUrl = false){

		if (!$imageId) {
			return false;
		}

    	import('Extend.Taobao.TaobaoGoods');
    	$taobao = new \TaobaoGoods();

    	$queryRes = $taobao->fetchInfo($buyUrl);

    	$imgRes = $queryRes['item_get_response']['item']['item_imgs']['item_img'];

    	if (count($imgRes) <= 0) {
    		return false;
    	}


    	$addIMG = array();

    	foreach ($imgRes as $K => $v) {

    		$addIMG[] = array(
    				'parent_id' => $imageId,
    				'path' => $v['url'],
    				'created' => NOW_TIME,
    			);
    	}

    	$this->table(tname('product_image'))->addAll($addIMG);

	}

}
