<?php
/**
 * @author hisoka
 */
namespace Admin\Model;
use Think\Model;

class ProductBrandModel extends Model{

	/**
	 * 品牌、分类总数
	 */
	public function getCateTotal(){
		return $this->count();
	}

	/**
	 * 获取品牌、分类列表
	 * @param array $queryData 查询条件
	 */
	public function getCateList($queryData = array()){

		return $this->page($queryData['page'])->order('id DESC')->select();

	}

	/**
	 * 检测品牌、分类名是否存在
	 * @param array $reqData 查询数据
	 * @param bool $isEdit 是否编辑状态
	 */
	public function checkCate($reqData = array(), $isEdit = false){
		$where = array(
				'type' => $reqData['type'],
				'name' => $reqData['name'],
			);
		
		if ($isEdit) {
			$where['id'] = array('NEQ', $reqData['id']);
		}

		$count = $this->where($where)->count();

		return $count > 0 ? false : true;
	}

	/**
	 * 根据id获取品牌、分类内容
	 * @param int $id 品牌、分类id
	 */
	public function getCateById($id = 0){
		return $this->where(array('id' => $id))->find();
	}

}
