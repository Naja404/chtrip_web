<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class UserInfoModel extends Model{

	/**
	 * 检测用户信息ssid
	 * @param string $ssid 用户标识
	 */
	public function checkUserInfo($ssid = false){
		
		$where = array(
				'user_id' => $ssid,
			);

		return $this->where($where)->count();
	}

    /**
     * 获取用户数据
     */
    public function getUserInfo($userId = false){
        $where = array(
                'user_id' => $userId,
            );
        
        $queryRes = $this->field('avatar,nickname,mobile,sex')->where($where)->find();

        $queryRes['sex'] = L('TEXT_SEX_'.$queryRes['sex']);

        $queryRes['mobile'] = hide_mobile($queryRes['mobile']);

        return $queryRes;
    }
}
