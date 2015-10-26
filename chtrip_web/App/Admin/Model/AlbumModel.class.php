<?php
/**
 * 专辑模型
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class AlbumModel extends Model{

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
