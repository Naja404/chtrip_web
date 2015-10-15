<?php
/**
 * 
 * @author hisoka
 *
 */
namespace Admin\Controller;
use Admin\Controller\AdminBasicController;
use Admin\Model\Album;

/**
 * 后台专辑控制器
 */
class AlbumController extends AdminBasicController {

    public $albumModel;

    // public $uploadModel;

    public function _initialize(){
        parent::_initialize();
        
        $this->uploadModel   = D('Upload');
        $this->albumModel = D('Album');
        $this->productModel = D('Product');
    }

    /**
     * 专辑列表
     *
     */
    public function listAlbum(){

        $count = $this->albumModel->getAlbumCount();
        
        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page' => $p.','.C('PAGE_LIMIT'),
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->albumModel->getAlbumList($data));

        $this->display('listAlbum');

    }

    public function addAlbum(){
        
        if (IS_POST) {


            $imageId = $this->_getIMGId();

            $pid = $this->albumModel->addAlbum($imageId);

            if (!$pid) {
                $this->error('专辑添加失败');
            }

            $this->success($statu, U('Album/listAlbum'));
        }
        $this->assign('type', $this->albumModel->getAlbumType());
        $this->display('addAlbum');
    }

    /**
     * 编辑专辑
     */
    public function editAlbum(){

        if (IS_POST) {

            $reqData = I('post.');

            $gid = $this->_getIMGId();

            if ($gid) $reqData['gid'] = $gid;

            $status = $this->albumModel->editAlbum($reqData);

            if (!$status) $this->error('专辑编辑失败');

            $this->success($statu, U('Album/editAlbum', array('aid' => $reqData['aid'])));
        }

        $aid = I('request.aid');

        $this->assign('type', $this->albumModel->getAlbumType());
        $this->assign('detail', $this->albumModel->getAlbumDetail($aid));
        $this->display('editAlbum');
    }

    /**
     * 删除专辑
     * @param int $aid 专辑id
     */
    public function delAlbum(){
        
        $jsonRes = array(
                'status' => 0,
                'msg' => '专辑删除失败,请重新登陆后重试',
            );

        if (IS_POST) {
            
            $aid = I('request.aid');

            $status = $this->albumModel->delAlbum($aid);

            if ($status) $this->ajaxReturn(array('status' => 1));
        }

        $this->ajaxReturn($jsonRes);
    }

    /**
     * 获取上传图片image_id
     *
     */
    private function _getIMGId(){
        
        $imgRes = $this->uploadModel->uploadFile();

        if ($imgRes['path']) {
            return $this->productModel->setIMGId($imgRes['path']);
        }

        return false;
    }

}
