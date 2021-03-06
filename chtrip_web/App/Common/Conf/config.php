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
    'DEFAULT_LANG'          =>  'ja-jp', // 默认语言
	'LANG_AUTO_DETECT' 		=> 	true, 	// 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        		=> 	'zh-cn,ja-jp', // 允许切换的语言列表 用逗号分隔
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

    'API_WEBSITE' => 'http://local-admin.atniwo.com',

    /* 分页设置 */
    'PAGE_LIMIT'                => 30,

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
			'SMTP_HOST'   => 'smtp.exmail.qq.com', //SMTP服务器
			'SMTP_PORT'   => '465', //SMTP服务器端口
			'SMTP_USER'   => 'info@nijigo.com', //SMTP服务器用户名
			'SMTP_PASS'   => '1q2w3e4r', //SMTP服务器密码
			'FROM_EMAIL'  => 'info@nijigo.com', //发件人EMAIL
			'FROM_NAME'   => 'NijiGo', //发件人名称
			'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
			'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
	),

	'FILE_TMP_PATH' => 'Public/tmp/',

	/*excel 数据导入配置*/
	'EXCEL_INSERT_CONF' => array(
            'maxSize'    =>    1024*1024*10,
            'rootPath'   =>    'Public/uploads/',
            'savePath'   =>    '',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('xls', 'xlsx'),
            'autoSub'    =>    true,
            'subName'    =>    array('date','Ymd'),
        ),

	/*excel 产品数据键值对*/
	'EXCEL_INSERT_PRODUCT_ARR' => array(
			'shop_name',
			'title_zh',
			'title_jp',
			'description_zh',
			'description_jp',
			'summary_zh',
			'summary_jp',
			'brand',
			'category',
			'price_zh',
			'price_jp',
			'image_url',
		),

	/*excel 商家数据键值对*/
	'EXCEL_INSERT_SHOP_ARR' => array(
			'name',
			'description',
			'pic_url',
			'address',
			'open_time',
			'tel',
			'avg_price',
			'avg_rating',
			'tag_name',
			'category',
			'area',
			'type',
		),

	// google 配置内容
	'GOOGLE_CONF' => array(
			'GEO_URL'          => 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
			'GEO_KEY'          => 'AIzaSyA0xWHwylqZ2i0oJWMcMt6Kaepxv14P_Lg',
			'STATIC_IMAGE_URL' => 'https://maps.googleapis.com/maps/api/staticmap?center=%s&scale=%s&markers=color:%s|label:!|%s&zoom=%s&size=%s&key=%s&language=%s',
			'STATIC_IMAGE_KEY' => 'AIzaSyAb4TVivdvWXYNauoZXTDxkpDtfv6WDg4I',
			'STATIC_IMAGE_CONF' => array(
					'scale'    => 1,
					'color'    => 'red',
					'zoom'     => 18,
					'size'	   => '400x300',
					'language' => 'zh-CN',
				),
		),

	// 'JPY' => '0.0558',
	'JPY' => '0.0625',
	'SHIPPING_WEIGHT' => '200',// 包装箱重量

	'API_ADD_ADDRESS_URL' => 'http://api.nijigo.com/User/addAddress/ssid/%s.html?t=%s',
	'API_EDIT_ADDRESS_URL' => 'http://api.nijigo.com/User/editAddress/ssid/%s/aid/%s.html?t=%s',

	'MAP_IMAGE' => 'Public/uploads/map/',

	'WXPAY_CONF' => array(
			'APP_ID'     => 'wx140bb397338ea49a',
			'MCH_ID'     => '1308432801',
			'FEE_TYPE'   => 'CNY',
			'LIMIT_PAY'  => 'no_credit',
			'TRADE_TYPE' => 'APP',
			'PACKAGE'    => 'Sign=WXPay',
			'NOTIFY_URL' => 'http://api.nijigo.com/Util/wxpay',
			'REQ_URL'    => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
		),

	'ALIPAY_CONF' => array(
			'PATH'        => 'Public/conf/',
			'PARTNER_ID'  => '2088121371189092',
			'SELLER_ID'   => 'jeffzhang@nijigo.com',
			'NOTIFY_URL'  => 'http://api.nijigo.com/Util/alipay',
			'SERVICE_APP' => 'mobile.securitypay.pay',
			'SERVICE_WAP' => 'alipay.wap.create.direct.pay.by.user',
			'WAP_URL'     => 'https://mapi.alipay.com/gateway.do?',
		),

	'SERVER_IP' => '47.89.27.226',

	'EMS_JAPAN' => 'https://trackings.post.japanpost.jp/services/srv/search/?requestNo1=%s&locale=ja&search.x=93&search.y=24',
	'SERVICE_TAX' => '0.00',
	'SHIP_URL' => 'http://api.nijigo.com/Util/shipInfo/ssid/%s/oid/%s.html',
	'PUBLISH_COMMENT_URL' => 'http://api.nijigo.com/User/pubComment/type/%s/id/%s/ssid/%s.html',
);
