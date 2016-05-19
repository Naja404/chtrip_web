<?php

return array(
  

    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'ch_admin_s_', //session前缀
    'COOKIE_PREFIX'  => 'ch_admin_c_', // Cookie前缀 避免冲突
    'VAR_SESSION_ID' => 'session_id',	//修复uploadify插件无法传递session_id的bug

    /* 默认设定 */
	'LANG_SWITCH_ON'		=>	true,	 //开启语言包功能
    'DEFAULT_LANG'          =>  'zh-cn', // 默认语言
	'LANG_AUTO_DETECT' 		=>   false, 	// 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        		=>  'zh-cn', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     		=>  'l', 	// 默认语言切换变量
    'DEFAULT_THEME'         =>  '',	// 默认模板主题名称
    'DEFAULT_MODULE'        =>  'Admin',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE'      =>  'PRC',	// 默认时区

    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
            'mimes'    => '', //允许上传的文件MiMe类型
            'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
            'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
            'autoSub'  => true, //自动子目录保存文件
            'subName'  => array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => './Public/uploads/images/', //保存根路径
            'savePath' => '', //保存路径
            'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'  => '', //文件保存后缀，空则使用原后缀
            'replace'  => false, //存在同名是否覆盖
            'hash'     => true, //是否生成hash编码
            'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）

    //缩略图尺寸
    'PICTURE_THUMB_SIZE'        =>  array(
            array('200','200'),//
    ),
    'IMAGES_PATH'    => './Public/uploads/images/',   //用户头像目录

    /* 模板引擎设置 */
    'TMPL_CONTENT_TYPE'     =>  'text/html', // 默认模板输出类型
    'TMPL_ACTION_ERROR'     =>  'Public:error', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  'Public:success', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  THINK_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件

    /* 样式设定 */
    'TMPL_PARSE_STRING' => array(
    			'__PUBLIC__' => '/Public/admin/',
    	),

    'ADMIN_WEBSITE' => 'http://local-admin.atniwo.com',

    /* AUTH 设定 */
    'AUTH_CONFIG'   => array(
            'AUTH_ON'           => true,                         //认证开关
            'AUTH_TYPE'         => 1,                            // 认证方式，1为时时认证；2为登录认证。
            'AUTH_GROUP'        => 'ch_admin_auth_group',        //用户组数据表名
            'AUTH_GROUP_ACCESS' => 'ch_admin_auth_group_access', //用户组明细表
            'AUTH_RULE'         => 'ch_admin_auth_rule',         //权限规则表
            'AUTH_USER'         => 'ch_admin_user'               //用户信息表
        ),
    /* 分页设置 */
    'PAGE_LIMIT'                => 15,


    /* cache 名称定义 */
    'CACHE' => array(
            'ADMIN_HEADER'           => 'ch_Admin:HeaderList',
            'ADMIN_LEFT_MENU'        => 'ch_Admin:LeftMenuByGid:',
            'ADMIN_PROFUCT_SHIPTYPE' => 'ch_Admin:Product:shiptype',
            'ADMIN_PRODUCT_SALE'     => 'ch_Admin:Product:sale',
            'ADMIN_PRODUCT_TAG'      => 'ch_Admin:Product:tag',
        ),

    'FETCH_CLASS' => array(
            'www_enjoytokyo_jp'    => 'Enjoytokyo',
            'app_xiaotaojiang_com' => 'XiaoTaoJiang',
            'm_bolome_com'         => 'Bolome',
            'm_maiyamall_com'      => 'MaiyaMall',
            'm_wandougongzhu_cn'   => 'WanDouGongZhu',
        ),

    'PREVIEW_ALBUM' => 'http://local-api.atniwo.com/Product/showAlbum?aid=%s',
    'PREVIEW_ALBUM_MCHA' => 'http://api.atniwo.com/Product/showMcha?id=%s',
);
