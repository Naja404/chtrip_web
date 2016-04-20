<?php
/**
 * @author hisoka
 */
namespace Api\Model;
use Think\Model;

class CommentModel extends Model{

    /**
     * 添加评论
     * @param array $comment 评论内容
     */
    public function addComment($comment = array()){
        return $this->add($comment);
    }

    /**
     * 获取商品评论
     * @param int $pid 商品id
     */
    public function getProductComment($pid = 0){
    	
    	$joinImg = tname('comment_image')." AS b ON b.cid = a.id";
    	$joinUser = tname('user_info')." AS c ON c.user_id = a.user_id";
    	$where = array(
    			'a.pid' => $pid,
    		);
    	$field = "a.comment, c.nickname AS name, b.path, c.avatar";
    	$queryRes = $this->table(tname('comment')." AS a")->field($field)->join($joinImg)->join($joinUser)->where($where)->select();

    	return $queryRes;
    }
}
