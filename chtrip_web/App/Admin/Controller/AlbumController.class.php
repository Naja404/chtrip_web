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
        
        $this->uploadModel  = D('Upload');
        $this->albumModel   = D('Album');
        $this->productModel = D('Product');

        $this->adType = array('1' => '产品', '2' => '景点', '3' => '专辑' , '4' => '外链');

        $this->ajaxRes = array(
                'status' => 1,
                'msg'    => L('error_operation'),
            );
    }

    /**
     * mcha rss 数据列表
     */
    public function mchaList(){
        
        $count = $this->albumModel->getMchaCount();
        
        $page = new \Think\Page($count, C('PAGE_LIMIT'));

        $p = I('request.p', 1);

        $data = array(
                    'page' => page($p, C('PAGE_LIMIT')),
            );

        $this->assign('page_show', $page->showAdmin());
        $this->assign('list', $this->albumModel->getMchaList($data));

        $this->display('mchaList');
    }

    /**
     * 发布rss
     */
    public function publishRss(){

        if (!IS_AJAX) $this->ajaxReturn($this->ajaxRes);

        $id = I('request.id');

        $status = $this->albumModel->publishRss($id);

        if ($status === true) $this->ajaxRes = array('status' => '0');

        $this->ajaxReturn($this->ajaxRes);
    }

    /**
     * 滚动栏目
     */
    public function listAd(){
        $this->assign('title', '滚动图列表');
        $this->assign('adType', $this->adType);
        $this->assign('list', $this->albumModel->getAdList());
        $this->display('listAd');
    }

    /**
     * 编辑滚动栏目       
     */
    public function editAd(){

        $aid = I('request.aid');

        if (IS_POST) {
            $reqData = I('request.');
            $reqData['aid'] = $aid;

            $imageId = $this->_getIMGId();

            if ($imageId) $reqData['image_id'] = $imageId;

            $status = $this->albumModel->editAd($reqData);

            if (!$status) $this->error('滚动图编辑失败');

            $this->success($statu, U('Album/listAd'));
        }

        $this->assign('title', '编辑滚动图');
        $this->assign('adType', $this->adType);
        $this->assign('detail', $this->albumModel->getAdDetail($aid));
        $this->display('editAd');
    }

    /**
     * 专辑按钮列表
     */
    public function listAlbumBTN(){
        $count = $this->albumModel->getAlbumBTNCount();
        
        $page = new \Think\Page($count, C('PAGE_LIMIT'));
        
        $p = I('request.p', 1);
        
        $data = array(
                'page' => $p.','.C('PAGE_LIMIT'),
            );

        $this->assign('title', '专辑按钮列表');
        $this->assign('list', $this->albumModel->getAlbumBTNList($data));
        $this->display('listAlbumBTN');
    }

    /**
     * 编辑专辑按钮
     */
    public function editAlbumBTN(){
        
        $id = I('request.id');

        if (IS_POST) {
            
            $reqData = I('request.');

            $status = $this->albumModel->editAlbumBTN($reqData);

            if (!$status) $this->error('专辑按钮编辑失败');

            $this->success($statu, U('Album/listAlbumBTN'));
        }

        $this->assign('title', '编辑专辑按钮');
        $this->assign('detail', $this->albumModel->getAlbumBTNDetail($id));
        $this->display('editAlbumBTN');
    }

    /**
     * 删除专辑按钮
     */
    public function delAlbumBTN(){
        
        if (!IS_AJAX) $this->ajaxReturn($this->ajaxRes);

        $id = I('request.id');

        $status = $this->albumModel->delAlbumBTN($id);

        if ($status === true) $this->ajaxRes = array('status' => '0');

        $this->ajaxReturn($this->ajaxRes);
    }

    /**
     * 新增专辑按钮
     */
    public function addAlbumBTN(){
        if (IS_POST) {
            
            $reqData = I('request.');

            $status = $this->albumModel->addAlbumBTN($reqData);

            if (!$status) $this->error('专辑按钮新增失败');

            $this->success($statu, U('Album/listAlbumBTN'));
        }

        $this->assign('title', '新增专辑按钮');
        $this->display('addAlbumBTN');
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
                    'page' => page($p, C('PAGE_LIMIT')),
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
        $this->assign('title_btn', $this->albumModel->getAlbumBTNList());
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
        $this->assign('title_btn', $this->albumModel->getAlbumBTNList());
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
