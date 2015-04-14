<?php
/**
 * 
 * Taobao 
 * @author Hisoka
 *
 */

class TaobaoGoods {
	
	/**
	 * 
	 * API URL地址
	 * @var string
	 */
	private $apiUrl;
	
	/**
	 * 
	 * API 秘钥
	 * @var string
	 */
	private $apiKey;
	
	private $apiSecret;
	
	public function __construct() {
		$this->apiUrl = 'http://gw.api.taobao.com/router/rest';
		$this->apiKey = '23025832';
		$this->apiSecret = 'c87d8417f86b0a7e77308c4982ede8c4';
	}
	
	/**
	 * 
	 * 获取商品信息
	 */
	public function fetchInfo($url = false) {
		
		$id = url_fetch_get_param($url, 'id');

        $params = array(
            'timestamp'=> date('Y-m-d H:i:s'),
        	'app_key' => $this->apiKey,
       		'method' => 'taobao.item.get',
        	'format' => 'json',
            'v' => '2.0',
        	'sign_method' => 'md5',
        	'fields' => 'detail_url,num_iid,title,nick,type,cid,seller_cids,props,input_pids,input_str,pic_url,num,valid_thru,list_time,delist_time,stuff_status,location,price,post_fee,express_fee,ems_fee,has_discount,freight_payer,has_invoice,has_warranty,has_showcase,modified,increment,approve_status,postage_id,product_id,auction_point,property_alias,item_img,prop_img,sku,video,outer_id,is_virtual',
        	'num_iid' => $id,
        );

        $api_data = $this->_requestApi($params);
		
		return $api_data;
	}
	
	/**
	 * 
	 * 获取商品图片列表
	 */
	public function fetchPhotoList($url = false) {
		
	}
	
	/**
	 * 
	 * 请求API
	 * @param array $params 请求参数
	 */
	public function _requestApi($params = array()) {
		
		ksort($params);
		
		$str = $str2 = '';
		
		foreach ($params as $k => $v) {
			$str2 .= $k.$v;
		}
		
		$params['sign'] = strtoupper(md5($this->apiSecret.$str2.$this->apiSecret));

		$tmp_param = array(
					'timestamp' => $params['timestamp'], 
			);

		unset($params['timestamp']);

		$params = array_merge_recursive($tmp_param, $params);

		foreach ($params as $k => $v) {
			$str .= $k.'='.urlencode($v).'&';
		}

		$url = $this->apiUrl.'?'.substr($str, 0, -1);

		$content = file_get_contents($url);
		$content = json_decode($content, true);

		if (empty($content['error_response'])) {
			return $content;
		}
		
		return false;
	}
}