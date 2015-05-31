<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;
defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache {
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPERT_').':redis');
        }
        if(empty($options)) {
            $options = array (
                'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
                'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
                'timeout'       => C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
                'persistent'    => false,
            );
        }
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire)) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flushDB();
    }

    /**
     * push消息队列
     * @access public
     * @param   string $path  队列目录
     * @param   json   $value 队列内容
     * @return  boolean
     * @author  Hisoka @2014-4-22
     */
    public function push($path = false, $value = false){
        $path = !empty($path) ? $path : C('REDIS_LIST_FETCH_EMAIL');

        if (empty($value)) {
            return false;
        }

        $result = $this->handler->LPUSH($path, $value);

        return $result;
    }

    /**
     * pop消息队列
     * @access public
     * @param string $path 队列目录
     * @return mixed
     * @author  Hisoka @2014-4-22
     */
    public function pop($path = false){
        $path = !empty($path) ? $path : C('REDIS_LIST_FETCH_EMAIL');

        $result = $this->handler->RPOP($path);

        return $result;
    }

    /**
     * 获取消息队列长度
     * @access public
     * @param string $path 队列目录
     * @return int
     * @author Hisoka @2014-5-22
     */
    public function size($path = false){
        return $this->handler->LSize($path);
    }

    /**
     * 获取树形keys下所有key
     * @access public
     * @param string $keys 树形keys
     * @return array
     * @author Hisoka @2014-7-30
     */
    public function keys($keys = false){
        return $this->handler->keys($keys);
    }

    /**
     * 设置cache过期时间
     * @access public
     * @param string $key cache名称
     * @param int  $expire 过期时间
     * @author Hisoka @2014-8-20
     */
    public function expire($key = false, $expire = 0){
        return $this->handler->expire($key, $expire);
    }

    /**
     * 获取cache ttl还剩多少
     * @access public
     * @param string $key cache名称
     * @author Hisoka @2014-8-20
     */
    public function getExpire($key = false){
        return $this->handler->ttl($key);
    }
	
    /**
     * 读取list区间内容,不出队列
     * @access public
     * @param string $key cache名
     * @param int $start 开始偏移量下标
     * @param int $stop 结束偏移量下标
     * @author Hisoka @2014-8-28
     */
    public function listRange($key = false, $start = 0, $stop = 1){
        return $this->handler->lRange($key, $start, $stop);
    }

    /**
     * 删除队列中指定内容
     * @access public
     * @param string $key cache名
     * @param string $value cache的value值
     * @param int $count 相同value的数量
     * @author Hisoka @2014-8-28
     */
    public function listRem($key = false, $value = false, $count = 1){
        return $this->handler->lRem($key, $value, $count);
    }

	/**
     * 返回key是否存在
     * @access public
     * @param string $key cache名称
     * @author Hisoka @2014-8-26
     */
    public function exists($key = false){
    	return $this->handler->exists($key);
    }

    /**
    *转化一些redis扩展的方法到Redis类上面
    *主要是对hash的处理
    *@$hashtable  要写入的hash表名
    *@$k          要写入的hash键
    *@$v          要写入的hash键对应的值
    */

   //存储hash类型的表键值
    public function hset($hashtable,$k,$v){
        return $this->handler->hSet($hashtable,json_encode($k),json_encode($v));
    }
    //判断是否存在键值
    public function hexists($hashtable,$k){
        return $this->handler->hExists($hashtable,json_encode($k));
    }
    //通过hashkey获取值
    public function hget($hashkey,$k){
        return $this->handler->hGet($hashkey,json_encode($k));
    }
    //取hash表内的键值数目
    public function hlen($hashtable){
        return $this->handler->hLen($hashtable);
    }
    //取得所有的hash键值
    public function hgetall($hashtable){
        return $this->handler->hGetAll($hashtable);
    }
    //取得所有的hash的key值
    public function hkeys($hashtable){
        return $this->handler->hKeys($hashtable);
    }
    //删除指定hash域的value
    public function hdel($hashtable,$key){
        return $this->handler->hDel($hashtable,json_encode($key));
    }
    //删除整个hash表
    public function hdelall($hashtable){
        $keys = $this->handler->hkeys($hashtable);
        foreach($keys as $k){
            $this->handler->hdel($hashtable,$k);
        }
    }

}
