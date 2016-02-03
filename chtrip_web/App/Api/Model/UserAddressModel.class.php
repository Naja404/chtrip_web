<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UserAddressModel extends Model{


    /**
     * 获取用户收货地址
     * @param string $userId 用户id
     */
    public function getUserAddress($userId = false){
        $where = array(
                'user_id' => $userId,
                'status'  => 1,
            );

        $queryRes = $this->field('id, name, mobile, address, default')->where($where)->select();

        foreach ($queryRes as $k => $v) {
            $queryRes[$k]['edit_url'] = sprintf(C('API_EDIT_ADDRESS_URL'), $userId, $v['id'], time());
            if ((int)$v['default'] == 1) $queryRes[$k]['address'] = L('TEXT_DEFAULT_ADDRESS').$v['address'];
        }

        return count($queryRes) <= 0 ? array() : $queryRes;
    }

    /**
     * 获取城市列表
     * @param int $id 城市id
     * @param int $level 城市等级 1.省 2.城市 3.区
     */
    public function getCityList($id = 0, $level = 1, $html = false){

        $where = array(
                'pid'   => $id,
                'level' => $level,
            );

        $queryRes = $this->table(tname('city'))->where($where)->select();

        if ($html) {
            
            $tmpArea = '';

            if ($level == 2) $tmpArea = $this->_formatCitySelect($this->getCityList($queryRes[0]['id'], 3));

            $queryRes = array(
                    'city' => $this->_formatCitySelect($queryRes),
                    'area' => $tmpArea, 
                );
        } 

        return $queryRes;
    }

    /**
     * 检测用户地址
     * @param array $reqData 请求数据
     */
    public function checkAddress($reqData = array()){
        
        $where = array(
                'id'      => $reqData['aid'],
                'user_id' => $reqData['ssid'],
                'status'  => 1,
            );

        $queryRes = $this->where($where)->count();

        return (int)$queryRes === 1 ? true : false;
    }

    /**
     * 新增地址
     * @param array $reqData 请求内容
     */
    public function setAddress($reqData = array()){

        $where = array(
                'default' => 1,
                'user_id' => $reqData['ssid'],
                'status'  => 1,
            );
        
        $count = $this->where($where)->count();

        $add = array(
                'user_id' => $reqData['ssid'],
                'name'    => $reqData['name'],
                'mobile'  => $reqData['mobile'],
                'pid'     => $reqData['province'],
                'cid'     => $reqData['city'],
                'aid'     => $reqData['area'],
                'address' => $reqData['address'],
                'post'    => $reqData['post'],
                'default' => $count > 0 ? 0 : 1,
                'created' => time(),
                'edit'    => time(),
                'status'  => 1,
            );

        $this->add($add);
    }

    /**
     * 保存
     * @param type item
     */
    public function saveAddress($reqData = array()){
        $where = array(
                'user_id' => $reqData['ssid'],
                'id'      => $reqData['aid'],
            );

        $save = array(
                'name'    => $reqData['name'],
                'mobile'  => $reqData['mobile'],
                'pid'     => $reqData['province'],
                'cid'     => $reqData['city'],
                'aid'     => $reqData['area'],
                'address' => $reqData['address'],
                'post'    => $reqData['post'],
                'edit'    => time(),
            );

        if ((int)$reqData['default'] == 1) {
            $save['default'] = 1;
            
            $defaultWhere = array(
                    'user_id' => $reqData['ssid'],
                );

            $this->where($defaultWhere)->save(array('default' => 0));
        }

        $this->where($where)->save($save);
    }

    /**
     * 获取地址详情
     * @param array $reqData 请求数据
     */
    public function getDetail($reqData = array()){
        $where = array(
                'id'      => $reqData['aid'],
                'user_id' => $reqData['ssid'],
                'status'  => 1,
            );

        $queryRes = $this->where($where)->find();

        return $queryRes;
    }

    /**
     * 删除收货地址
     * @param array $reqData 请求数据
     */
    public function delAddress($reqData = array()){
        $where = array(
                'user_id' => $reqData['ssid'],
                'id'      => $reqData['id'],
            );

        $save = array(
                'status' => 0,
            );

        return $this->where($where)->save($save);
    }

    private function _formatCitySelect($data = array()){
        $select = "<option value='%s'>%s</option>";

        foreach ($data as $k => $v) {
            $selectHtml .= sprintf($select, $v['id'], $v['name']);
        }

        return $selectHtml;
    }
}
