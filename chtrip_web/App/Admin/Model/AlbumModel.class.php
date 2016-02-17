<?php
/**
 * 专辑模型
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class AlbumModel extends Model{

	const DB_MCHA = 'ch_mcha';
	const DB_IMAGE = 'ch_product_image';

	public function _initialize(){

	}

	/**
	 * 获取mcha总数据
	 */
	public function getMchaCount(){
		return $this->table(self::DB_MCHA)->count();
	}

	/**
	 * 获取mcha数据
	 * @param array $data 查询数据
	 */
	public function getMchaList($data = array()){
		
		$join = self::DB_IMAGE." AS B ON B.gid = A.image_id ";

		$field = "A.*, B.path";

		$queryRes = $this->field($field)->table(self::DB_MCHA.' AS A')->join($join)->limit($data['page'])->select();

		return $queryRes;
	}

	/**
	 * 获取滚动列表
	 */
	public function getAdList(){
		$sql = "SELECT a.id, a.type,a.title, a.url,a.url_id,b.path,
					(CASE a.type 
						WHEN 1 THEN (SELECT title_zh FROM ch_product_detail_copy WHERE pid = a.url_id LIMIT 1)
						WHEN 2 THEN (SELECT name FROM ch_saler WHERE saler_id = a.url_id LIMIT 1) 
						WHEN 3 THEN (SELECT title FROM ch_album WHERE id = a.url_id LIMIT 1) 
						ELSE ''
					 END) AS title_zh  FROM ch_ad AS a 
			LEFT JOIN ch_product_image AS b ON b.gid = a.image_id
			ORDER BY a.sort ASC";
		return $this->query($sql);
	}

	/**
	 * 获取滚动内容详情
	 * @param int $id 滚动图片id
	 */
	public function getAdDetail($id = false){
		$sql = "SELECT a.id, a.type,a.title, a.url,a.url_id,b.path,
						(CASE a.type 
							WHEN 1 THEN (SELECT title_zh FROM ch_product_detail_copy WHERE pid = a.url_id LIMIT 1)
							WHEN 2 THEN (SELECT name FROM ch_saler WHERE saler_id = a.url_id LIMIT 1) 
							WHEN 3 THEN (SELECT title FROM ch_album WHERE id = a.url_id LIMIT 1) 
							ELSE ''
						 END) AS title_zh FROM ch_ad AS a 
				LEFT JOIN ch_product_image AS b ON b.gid = a.image_id
				WHERE a.id = '".$id."' LIMIT 1";

		$queryRes = $this->query($sql);

		return $queryRes[0];
	}

	/**
	 * 编辑滚动图片
	 * @param array $reqData 请求内容
	 */
	public function editAd($reqData = array()){
		$update = array(
				'type'  => intval($reqData['type']),
				'title' => $reqData['title'],
				'sort'  => intval($reqData['sort']),
			);

		if ($reqData['image_id']) $update['image_id'] = $reqData['image_id'];

		if ($reqData['type'] == 4) {
			$update['url'] = $reqData['url'];
		}else{
			$update['url_id'] = intval($reqData['url_id']);
		}

		$where = "`id` = '".$reqData['aid']."'";

		foreach ($update as $k => $v) {
			$updateSql[] = "`".$k."` = '".$v."'";
		}
		
		$updateSql = implode(', ', $updateSql);

		$sql = "UPDATE ".tname('ad')." SET ".$updateSql." WHERE ".$where;
		
		return $this->execute($sql);

		// return $this->table(tname('ad'))->where($where)->save($update);
	}	

	/**
	 * 获取专辑按钮总数
	 */
	public function getAlbumBTNCount(){
		return $this->table(tname('album_btn'))->count();
	}

	/**
	 * 获取专辑按钮列表
	 */
	public function getAlbumBTNList(){
		return $this->table(tname('album_btn'))->select();
	}

	/**
	 * 获取专辑按钮详情
	 * @param int $id 专辑按钮id
	 */
	public function getAlbumBTNDetail($id = false){
		$where = array(
				'id' => intval($id),
			);
		return $this->table(tname('album_btn'))->where($where)->find();
	}

	/**
	 * 编辑专辑按钮	
	 * @param array $reqData 请求内容
	 */
	public function editAlbumBTN($reqData = array()){

		if (empty($reqData['name'])) return false;

		$sql = "UPDATE ".tname('album_btn')." SET `name` = '".$reqData['name']."' WHERE id = '".intval($reqData['id'])."' ";

		return $this->execute($sql);
	}

	/**
	 * 删除专辑按钮
	 * @param int $id 专辑按钮id
	 */
	public function delAlbumBTN($id = false){
		$where = array(
				'id' => (int)$id,
			);

		$queryRes = $this->table(tname('album_btn'))->where($where)->delete();

		return $queryRes ? true : false;
	}

	/**
	 * 新增专辑按钮 
	 * @param array $reqData 请求内容
	 */
	public function addAlbumBTN($reqData = array()){
		if (empty($reqData['name'])) return false;

		$sql = "INSERT INTO `".tname('album_btn')."` (`name`) VALUES ('".$reqData['name']."')";

		return $this->execute($sql);
	}

	/**
	 * 添加专辑
	 * @param int $gid 图片id
	 */
	public function addAlbum($gid = 0){

		$add = array(
				'title'         => I('request.title'),
				'title_btn'     => I('request.title_btn'),
				'type'          => I('request.type'),
				'gid'           => $gid,
				'address_title' => I('request.address_title', ''),
				'start_time'    => strtotime(I('request.start_time')),
				'end_time'      => strtotime(I('request.end_time')),
				'sort'          => I('request.sort'),
				'recommend'     => I('request.recommend', 0),
				'content'       => htmlspecialchars(I('request.content')),
				'status'        => 1,
				'create_time'   => time(),
				'update_time'   => time(),
			);

		return $this->add($add);

	}

	/**
	 * 编辑专辑		
	 * @param array $reqData 编辑数据内容
	 */
	public function editAlbum($reqData = array()){
		
		$edit = array(
				'title'       => I('request.title'),
				'title_btn'   => I('request.title_btn'),
				'type'        => I('request.type'),
				'address_title' => I('request.address_title', ''),
				'start_time'  => strtotime(I('request.start_time')),
				'end_time'    => strtotime(I('request.end_time')),
				'sort'        => I('request.sort'),
				'recommend'   => I('request.recommend', 0),
				'content'     => htmlspecialchars(I('request.content')),
				'update_time' => time(),
			);

		if ($reqData['gid']) $edit['gid'] = $reqData['gid'];

		$where = array(
				'id' => $reqData['aid'],
			);

		return $this->where($where)->save($edit);
	}

	/**
	 * 删除专辑
	 * @param int $aid 专辑id
	 */
	public function delAlbum($aid = 0){
		$where = array(
				'id' => $aid,
			);

		$save = array(
				'status' => 0,
			);
		return $this->where($where)->save($save);
	}

	/**
	 * 获取专辑详情内容
	 * @param int $aid 专辑id
	 */
	public function getAlbumDetail($aid = 0){

		$sql = "SELECT a.*, b.name AS type_name, c.path  FROM ch_album AS a
						LEFT JOIN ch_album_type AS b ON b.id = a.type
						LEFT JOIN ch_product_image AS c ON c.gid = a.gid
						WHERE a.id = %s LIMIT 1";
		$queryRes = $this->query(sprintf($sql, $aid));

		$queryRes[0]['content'] = htmlspecialchars_decode($queryRes[0]['content']);

		return $queryRes[0];
	}

	/**
	 * 获取专辑数据总数
	 *
	 */
	public function getAlbumCount(){
		return $this->where(array('status' => 1))->count();
	}

	/**
	 * 获取专辑数据
	 * @param array $data 查询条件
	 */
	public function getAlbumList($data = array()){

		$sql = "SELECT a.*, b.path, c.name AS typename FROM ch_album AS a 
				LEFT JOIN ch_product_image AS b ON b.gid = a.gid 
				LEFT JOIN ch_album_type AS c ON c.id = a.type 
				WHERE a.status = 1 
				ORDER BY a.id DESC
				LIMIT ".page($data['page'], 15);

		$queryRes = $this->query($sql);

		return $queryRes;
	}

	/**
	 * 获取专辑分类列表
	 *
	 */
	public function getAlbumType(){
		return $this->table(tname('album_type'))->select();
	}

}
