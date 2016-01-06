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

        return array();
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

    private function _formatCitySelect($data = array()){
        $select = "<option value='%s'>%s</option>";

        foreach ($data as $k => $v) {
            $selectHtml .= sprintf($select, $v['id'], $v['name']);
        }

        return $selectHtml;
    }
}
