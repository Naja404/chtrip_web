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

        $queryRes = $this->field('name, mobile, address, default')->where($where)->select();

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

    private function _formatCitySelect($data = array()){
        $select = "<option value='%s'>%s</option>";

        foreach ($data as $k => $v) {
            $selectHtml .= sprintf($select, $v['id'], $v['name']);
        }

        return $selectHtml;
    }
}
