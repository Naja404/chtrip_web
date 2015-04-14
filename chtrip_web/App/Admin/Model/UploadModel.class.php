<?php
namespace Admin\Model;
use Think\Model;
use Think\Upload;
use Think\Image;

/**
 * 上传模型
 */

class UploadModel extends Model{
	
	/**
	 * 上传对象
	 * @var $upload
	 */
	protected $upload;
	
	protected $files;
	
	// 使用对上传图片进行缩略图处理
	protected $thumb  =  false;

	// 缩略图最大宽度
	protected $thumbMaxWidth;
	
    // 缩略图最大高度
	protected $thumbMaxHeight;
	
    // 缩略图保存路径
	protected $thumbPath;
	
    // 是否移除原图
	protected $thumbRemoveOrigin = false;
	
	//返回标准数据
	public $result  = array('state' => 0);
	
	/**
	 * 初始化
	 */
	public function __construct(){

		$this->upload = new Upload(C('PICTURE_UPLOAD'));
		
		//缩略图尺寸
		$size = C('PICTURE_THUMB_SIZE.0');
		$this->thumbMaxWidth  = $size[0];
		$this->thumbMaxHeight = $size[1];
		$this->thumb = true;
	
	}

    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @return array           文件上传成功后的信息
     */
    public function uploadFile(){
    	//检查上传文件是否完整
    	if(!$this->check_files()) return $this->result;
        /* 上传文件 */
        $files   = array_values($this->upload->upload());
        $this->files = $files[0];
		
        if($this->files){ //文件上传成功，记录文件信息

        	$fileUrl = C('API_WEBSITE').str_replace('./','/',C('IMAGES_PATH')).$this->files['savepath'].$this->files['savename'];
        	
            $fileInfo = array(
        			'url'  => $fileUrl,
                    'path' => str_replace('./','/',C('IMAGES_PATH')).$this->files['savepath'].$this->files['savename'],
        	);

            $this->result = array_merge($this->result,$fileInfo);
            
            //生成缩略图
            if($this->thumb)
            	$this->thumb();
            
        } else {
            $this->result['error']   = $this->upload->getError();
            $this->result['state'] = 1;
        }

        return $this->result;
    }
    
    /**
     * 生成缩略图
     */
    public function thumb($files = ''){
    	if($files){
    		$fileInfo = getimagesize($files);
    		$this->files['ext'] = str_replace('image/', '', $fileInfo['mime']);
    	}
    	
    	if(!in_array(strtolower($this->files['ext']),array('gif','jpg','jpeg','bmp','png'))) E('非法图片格式');
    	
    	//图片地址
    	$imgname = $files ? files : C('PICTURE_UPLOAD.rootPath').$this->files['savepath'].$this->files['savename'];
    	
    	if(false == getimagesize($imgname)) E('非法图像资源');
    	 
    	//是图像文件生成缩略图
        $thumbWidth  =	explode(',',$this->thumbMaxWidth);
        $thumbHeight =	explode(',',$this->thumbMaxHeight);
        $thumbPrefix =	explode(',',$this->thumbPrefix);
        $thumbSuffix = explode(',',$this->thumbSuffix);
        $thumbFile   =	explode(',',$this->thumbFile);
        $thumbPath   =  $this->thumbPath?$this->thumbPath:C('PICTURE_UPLOAD.rootPath').$this->files['savepath'];
    	
    	// 生成图像缩略图
    	$image = new Image();
    	for($i=0,$len=count($thumbWidth); $i<$len; $i++) {
    		$thumbSuffix = '_'.$thumbWidth[$i].'_'.$thumbHeight[$i];
    		$thumbname	=	$thumbPath.substr($this->files['savename'],0,strrpos($this->files['savename'], '.')).$thumbSuffix.'.'.$this->files['ext'];
    	
    		$image->open($imgname);
    		$image->thumb($thumbWidth[$i],$thumbHeight[$i])->save($thumbname);
    	}
    	if($this->thumbRemoveOrigin) {
    		// 生成缩略图之后删除原图
    		unlink($imgname);
    	}
    	
    }
    /**
     * 检查上传文件是否完整
     */
    private function check_files(){
    	
        $files = $this->dealFiles($_FILES);

        if (!is_array($files)) {
            return false;
        }
    	return true;
    }
    /**
     * 转换上传文件数组变量为正确的方式
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function dealFiles($files) {
    	$fileArray  = array();
    	$n          = 0;
    	foreach ($files as $key=>$file){
    		if(is_array($file['name'])) {
    			$keys       =   array_keys($file);
    			$count      =   count($file['name']);
    			for ($i=0; $i<$count; $i++) {
    				$fileArray[$n]['key'] = $key;
    				foreach ($keys as $_key){
    					$fileArray[$n][$_key] = $file[$_key][$i];
    				}
    				$n++;
    			}
    		}else{
    			$fileArray[$key] = $file;
    		}
    	}
    	return $fileArray;
    }

}
