#宝贝GO API接口说明文档

|版本|作者|修改时间|
|---|---|---|
|v1.0|<a href="mailto:hisoka.2pac@gmail.com" target="_blank">Hisoka</a>|2014-9-24

##摘要说明
###请求地址
```
	http://api.babyjiayou.com/
```
###通用返回参数
|字段|说明|
|---|---|
|status|0.正常 1.异常
|error|当status为1时,error显示,报错信息

###通用返回header参数
|字段|说明|
|---|---|
|UUID|设备标识，由客户端header中带入，如不存在则返回新UUID
|userId|用户id，根据uuid获得


##产品模块

###产品列表
```
	Product/proList
```
####请求内容
|字段|是否必须|类型及范围|说明|
|---|---|---|---|
|tag|false|string|筛选产品tag属性
|pageSize|false|int|显示数据条数
|pageNum|false|int|页数

####返回数据
|字段|说明|
|---|---|
|proList|产品数据内容父级下标
|pid|产品id
|title|产品标题
|price|价格
|comments|评论数
|sales|销量
|views|浏览量
|buy_url|购买链接
|path|产品原图路径
|thumb|产品缩略图路径
|shipping_name|邮寄规则
|tag_name|标签数组
|sale_name|店铺名称
|sale_url|店铺url
|created|产品创建时间
|sort|排序值,越大越靠前
|recommend|推荐状态 1.推荐 0.无
|hasMore|1.有更多 0.没有更多

###返回示例
####请求示例
```
	http://api.babyjiayou.com/Product/proList?tag=2,5
```

```
{
	status: 0,
	proList: [
		{
			pid: "4",
			title: "Geek Power 2014新款",
			price: "99",
			comments: "1193",
			sales: "804",
			views: "893",
			buy_url: "http://item.taobao.com/item.htm?spm=a1z10.1.w5003-2961421267.1.xWAmVe&amp;id=39790935726&amp;scene=taobao_shop",
			path: "http://api.bj.com/Public/uploads/images/20140924/5422296cd3666.jpg",
			thumb: "http://api.bj.com/Public/uploads/images/20140924/5422296cd3666_100_100.jpg",
			shipping_name: "江浙沪包邮",
			tag_name: [
			"男宝",
			"女宝",
			"淘宝"
			],
			sale_name: "喔喔",
			sale_url: "http://taobao.com",
			created: "1411524972",
			sort: "1212",
			recommend: "1"
		},
		{
			pid: "15",
			title: "Sheldon闪电侠假两件t恤-黑色版",
			price: "89.9",
			comments: "802",
			sales: "1166",
			views: "1168",
			buy_url: "http://item.taobao.com/item.htm?spm=a1z10.1.w4004-5164293753.12.ZBnNyG&amp;id=12734995831",
			path: "http://api.bj.com/Public/uploads/images/20140924/5422606d4ad18.jpg",
			thumb: "http://api.bj.com/Public/uploads/images/20140924/5422606d4ad18_100_100.jpg",
			shipping_name: "免邮",
			tag_name: [
			"天猫",
			"京东"
			],
			sale_name: "喔喔",
			sale_url: "http://taobao.com",
			created: "1411539053",
			sort: "123",
			recommend: "1"
		}
	],
	hasMore: "0"
}
```
###产品详情
```
	Product/proDetail
```
####请求内容
|字段|是否必须|类型及范围|说明|
|---|---|---|---|
|pid|true|int|产品id

####返回数据
|字段|说明|
|---|---|
|proDetail|产品数据内容父级下标
|pid|产品id
|title|产品标题
|description|产品描述,含html标签
|price|价格
|comments|评论数
|sales|销量
|views|浏览量
|buy_url|购买链接
|path|产品原图路径
|thumb|产品缩略图路径
|shipping_name|邮寄规则
|tag_name|标签数组
|sale_name|店铺名称
|sale_url|店铺url
|created|产品创建时间
|sort|排序值,越大越靠前
|recommend|推荐状态 1.推荐 0.无


###返回示例
####请求示例
```
	http://api.babyjiayou.com/Product/proDetail?pid=6
```

```
{
	status: 0,
	proDetail: {
		pid: "6",
		title: "Geek Power 2014新款",
		description: "<a href="">Geek Power 2014新款</a>",
		price: "90",
		comments: "975",
		sales: "949",
		views: "860",
		buy_url: "http://item.taobao.com/item.htm?spm=a1z10.1.w4004-2960748353.2.ZBnNyG&amp;id=36178340962",
		path: "http://api.bj.com/Public/uploads/images/20140924/54222a7cc4da1.jpg",
		thumb: "http://api.bj.com/Public/uploads/images/20140924/54222a7cc4da1_100_100.jpg",
		shipping_name: "免邮",
		tag_name: [
		"男宝",
		"天猫"
		],
		sale_name: "喔喔",
		sale_url: "http://taobao.com",
		created: "1411525244",
		sort: "3",
		recommend: "1"
	}
}
```

###图片列表
```
	Product/proIMG
```
####请求内容
|字段|是否必须|类型及范围|说明|
|---|---|---|---|
|gid|true|int|图片的父级id,来自产品pid

####返回数据
|字段|说明|
|---|---|
|imgList|图片数据父级下标


###返回示例
####请求示例
```
	http://api.babyjiayou.com/Product/proIMG?gid=43
```
```
{
	status: 0,
	imgList: [
		"http://gd4.alicdn.com/imgextra/i4/93353928/T2xbWuXAJaXXXXXXXX_!!93353928.jpg",
		"http://gd1.alicdn.com/imgextra/i1/93353928/T2M7CJXytXXXXXXXXX_!!93353928.jpg",
		"http://gd1.alicdn.com/imgextra/i1/93353928/T2CwXWXideXXXXXXXX_!!93353928.jpg",
		"http://gd1.alicdn.com/imgextra/i1/93353928/T2hWp3XjXeXXXXXXXX_!!93353928.jpg"
	]
}
```
##工具模块

###设置token
```
	Util/setToken
```
####请求内容
|字段|是否必须|类型及范围|说明|
|---|---|---|---|
|token|true|string|设备token
|type|true|int|1.iOS 2.android
|version|false|string|app版本号
|user_agent|false|string|设备标识

####返回数据
|字段|说明|
|---|---|
|status|0.成功 1.失败 

###返回示例
####请求示例
```
	http://api.babyjiayou.com/Util/setToken?type=iOS&version=1.0&token=1q2w3e4r5t6y7u8i9o&user_agent=123-33
```

```
{
	status: 0
}
```
