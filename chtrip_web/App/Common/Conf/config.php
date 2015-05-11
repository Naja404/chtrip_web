<?php

defined('THINK_PATH') or exit();

return  array(
	'APP_GROUP_LIST'		=> 'Home,Api,Admin',
	'MODULE_ALLOW_LIST'     => array('Home','Api','Admin'), // 配置你原来的分组列表
	'DEFAULT_GROUP'			=> 'Home',
	'APP_GROUP_MODE'		=> 1,

	/* URL设置 */
	'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL'             =>  0,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：

	'APP_SUB_DOMAIN_DEPLOY' =>1, // 开启子域名配置

	'APP_SUB_DOMAIN_RULES'  => array(

			'local-admin' => array('Admin/'),   // admin域名指向Admin分组
			'local-api'   => array('Api/'),		// api域名指向Api分组
	),

	/* 路由设置 */
	'URL_ROUTER_ON' => true,			// 开启路由
	'URL_ROUTE_RULES' => array(			//路由定义

	),

    /* 默认设定 */
	'LANG_SWITCH_ON'		=>	true,	 //开启语言包功能
    'DEFAULT_LANG'          =>  'zh-cn', // 默认语言
	'LANG_AUTO_DETECT' 		=> 	true, 	// 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        		=> 	'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     		=> 	'l', 	// 默认语言切换变量
    'DEFAULT_THEME'         =>  'default',	// 默认模板主题名称
    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称
    'DEFAULT_CHARSET'       =>  'utf-8', // 默认输出编码
    'DEFAULT_TIMEZONE'      =>  'PRC',	// 默认时区

	/*数据库设置 */
	'DB_TYPE'               =>  'mysql',     	// 数据库类型
	'DB_HOST'               =>  '127.0.0.1',    // 服务器地址
	'DB_NAME'               =>  'chtrip',     // 数据库名
	'DB_USER'               =>  'root',         // 用户名
	'DB_PWD'                =>  '',        		// 密码
	'DB_PORT'               =>  '3306',         // 端口
	'DB_PREFIX'             =>  'ch_',         // 数据库表前缀
	'DB_SUFFIX'             => '',             // 数据库表后缀


	/* Cookie设置 */
	'COOKIE_EXPIRE'         =>  0,    // Cookie有效期
	'COOKIE_DOMAIN'         =>  '',      // Cookie有效域名
	'COOKIE_PATH'           =>  '/',     // Cookie路径
	'COOKIE_PREFIX'         =>  'ch',      // Cookie前缀 避免冲突

	/* Redis设置*/
	'REDIS_HOST'      =>  '127.0.0.1',
	'REDIS_PORT'	  =>  '6379',

    'REDIS_CTYPE'           => 1, //连接类型 1:普通连接 2:长连接
    'REDIS_TIMEOUT'         => 0, //连接超时时间(S) 0:永不超时

    'DATA_CACHE_TIME'       => 0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   => false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      => false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     => '',     // 缓存前缀
    'DATA_CACHE_TYPE'       => 'Redis',  // 数据缓存类型,

    'API_WEBSITE' => 'http://admin.atniwo.com',

    /* 分页设置 */
    'PAGE_LIMIT'                => 15,

    /* CACHE_LIST */
    'CACHE_LIST' => array(
			'PRODUCT_COUNT'  => 'ch_Product:product_count',
			'PRODUCT_LIST'   => 'ch_Product:product_list:',
			'PRODUCT_IMG'    => 'ch_Product:product_img:',
			'PRODUCT_DETAIL' => 'ch_Product:product_detail:',
			'UTIL_TOKEN'     => 'ch_Util:device_token:',
    	),

	/*邮件配置*/
	'THINK_EMAIL' => array(
			'SMTP_HOST'   => 'smtp.qq.com', //SMTP服务器
			'SMTP_PORT'   => '465', //SMTP服务器端口
			'SMTP_USER'   => '2768524738@qq.com', //SMTP服务器用户名
			'SMTP_PASS'   => '1q2w3e4r', //SMTP服务器密码
			'FROM_EMAIL'  => '2768524738@qq.com', //发件人EMAIL
			'FROM_NAME'   => 'NijiGo', //发件人名称
			'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
			'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
	),

);
