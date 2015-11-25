<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class ProductModel extends Model{

	/**
	 * 查询内容
	 * @param int $type 0.商品 1.店铺
	 * @param string $name 查询内容
	 * @param bool $isHTML 是否格式化html
	 */
	public function searchProAjax($type = 0, $name = false, $isHTML = false){

		if ($type == 0) {
			$where = array(
					'title_zh' => array('LIKE', '%'.$name.'%'),
				);
			$field = 'title_zh';
			$id = 'pid';
			$queryRes = $this->table(tname('product_detail_copy'))
					->where($where)
					->limit(10)
					->select();
		}else{
			$where = array(
					'name' => array('LIKE', '%'.$name.'%'),
					'status' => 1,
				);
			$field = 'name';
			$id = 'saler_id';
			$queryRes = $this->table(tname('saler'))->where($where)->limit(10)->select();
		}

		if (count($queryRes) <= 0) return false;

		if ($isHTML) {
			return $this->_formatSearchResToHTML($queryRes, $field, $id);
		}

		return count($queryRes) <= 0 ? false : $queryRes;
	}

	/**
	 * 获取产品总量
	 *
	 */
	public function getProductCount(){
		return $this->table(tname('products_copy'))->where(array('status' => 1))->count();
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
				tname('product_detail_copy').' AS b ON b.pid = a.pid',
				tname('product_image').' AS c ON c.gid = a.image_id ',
			);

		$field = "b.pid,
					b.title_zh,
					b.summary_zh,
					b.brand,
					b.category,
					b.price_zh,
					b.price_jp,
					c.path,
					a.created";

		$order = ' a.created DESC ';

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
				'saler_id'  => intval(I('post.saler_id', 1)),
				'created'   => NOW_TIME,
			);

		$pid = $this->table(tname('products_copy'))->add($addData);

		if (!$pid) {
			return false;
		}

		$detailID = $this->_addProductDetail($pid);

		if (!$detailID) {
			
			$this->table(tname('products_copy'))->where(array('pid' => $pid))->delete();

			return false;
		}

		// $this->_getBuyUrlIMG($imageId, $_REQUEST['buy_url']);

		return $pid;

	}

	/**
	 * 删除产品	
	 * @param intval $pid 产品id
	 */
	public function delPro($pid = 0){

		$where = array(
				'pid' => (int)$pid,
			);
		$save = array(
				'status' => 0,
			);

		$queryRes = $this->table(tname('products_copy'))->where($where)->save($save);

		return $queryRes ? true : false;
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

		// if (cache(C('CACHE.ADMIN_PRODUCT_SHIPTYPE'))) {
		// 	return cache(C('CACHE.ADMIN_PRODUCT_SHIPTYPE'));
		// }

		$queryRes = $this->table(tname('product_shipping'))->field('id,name')->select();

		// if (count($queryRes) && is_array($queryRes)) {
		// 	cache(C('CACHE.ADMIN_PRODUCT_SHIPTYPE'), $queryRes);
		// }

		return $queryRes;
	}

	/**
	 * 获取店铺列表
	 *
	 */
	public function getSaler(){
		
		// if (cache(C('CACHE.ADMIN_PRODUCT_SALE'))) {
		// 	return cache(C('CACHE.ADMIN_PRODUCT_SALE'));
		// }

		$where = array(
				'status' => 1,
			);

		$queryRes = $this->table(tname('saler'))->field('saler_id, name')->where($where)->select();

		if (count($queryRes) && is_array($queryRes)) {
			// cache(C('CACHE.ADMIN_PRODUCT_SALE'), $queryRes);
		}

		return $queryRes;
	}

	/**
	 * 获取tag列表
	 *
	 */
	public function getTags(){

		// if(cache(C('CACHE.ADMIN_PRODUCT_TAG'))){
		// 	return cache(C('CACHE.ADMIN_PRODUCT_TAG'));
		// }

		$queryRes = $this->table(tname('product_tag'))->field('tid,name')->select();

		if (count($queryRes) && is_array($queryRes)) {
			// cache(C('CACHE.ADMIN_PRODUCT_TAG'), $queryRes);
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
				'pid'            => $pid,
				'title_zh'       => trim(I('post.titleZH')),
				'title_jp'       => trim(I('post.titleJP')),
				'description_zh' => I('post.descriptionZH', '', 'htmlspecialchars'),
				'description_jp' => I('post.descriptionJP', '', 'htmlspecialchars'),
				'price_zh'       => trim(I('post.priceZH')),
				'price_jp'       => trim(I('post.priceJP')),
				'shipping_type'  => I('post.shippingTypeZH'),
				'comments'       => I('post.comments'),
				'sales'          => I('post.sales'),
				'views'          => I('post.views'),
				'buy_url'        => trim(I('post.buy_urlZH')),
				'created'        => NOW_TIME,
			);

		$detailID = $this->table(tname('product_detail_copy'))->add($addDetail);

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

	/**
	 * 格式化查询数据为html
	 * @param array $queryRes 查询结果
	 * @param string $field 字段
	 */
	private function _formatSearchResToHTML($queryRes = array(), $field = 'name', $id = 'pid'){
		
		$html = '';

		foreach ($queryRes as $k => $v) {
			$html .= '<input id="pro_'.$k.'" type="radio" name="proId" value="'.$id.'_'.$v[$id].'">';
			$html .='<label for="pro_'.$k.'">'.$v[$field].'</label><br>';
		}

		return $html;
	}

}
