<?php

namespace helpers\Cache;

/**
 * Redis缓存驱动
 * 要求安装phpredis扩展：https://github.com/owlient/phpredis
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 * @author    尘缘 <130775@qq.com>
 */
class CacheRedis extends Cache {

    public $redis;

    /**
     * 架构函数
     * @access public
     */
    public function __construct($options = '') {
        if (!extension_loaded('redis')) {
            throw new Exception('redis扩展没安装');
        }
        if (empty($options)) {
             $options = array(
                 'host' => '127.0.0.1',
                 'port' =>  6379,
                 'timeout' => 10,
                 'persistent' => false,
                 'expire' => 0,
                 'length' => 0,
             );
        }

        $this->options = $options;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->redis = new \Redis();
        $this->connected = $options['timeout'] === false ?
        $this->redis->$func($options['host'], $options['port']) :
        $this->redis->$func($options['host'], $options['port'], $options['timeout']);
      //  $this->redis->auth('redis123456!@#$%^');
    }

    /**
     * 是否连接
     * @access private
     * @return boolen
     */
    private function isConnected() {
        return $this->connected;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        return $this->redis->get($name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null) {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire) {
            $result = $this->redis->setex($name, $expire, $value);
        } else {
            $result = $this->redis->set($name, $value);
        }
        if ($result && $this->options['length'] > 0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        return $this->redis->delete($name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->redis->flushDB();
    }

    /*
     * set添加
     */

    public function sAdd($key, $value) {
        return $this->redis->sAdd($key, $value);
    }

    /*
     * set删除名称为key的set中的元素value 
     */

    public function sRem($key, $value) {
        return $this->redis->sRem($key, $value);
    }

    /*
     * 名称为key的集合中查找是否有value元素，有ture 没有false 
     */

    public function sIsMember($key, $value) {
        return $this->redis->sIsMember($key, $value);
    }

    /*
     * set key个数
     */

    public function sSize($key) {
        return $this->redis->sSize($key);
    }

    /*
     * 返回set所有元素
     */

    public function sMembers($key) {
        return $this->redis->sMembers($key);
    }

    /*
     * 获取set 排序
     * $order desc asc
     */

    public function sSort($key, $order = 'asc', $start = 0, $end = 0) {
        if ($end > 0) {
            return $this->redis->sort($key, array('sort' => $order, 'limit' => array($start, $end)));
        }
        return $this->redis->sort($key, array('sort' => $order));
    }

    /*
     * 添加hash值
     */

    public function hSet($name, $key, $value) {
        $this->redis->hSet($name, $key, $value);
    }

    /*
     * 获取hash值
     */

    public function hGet($name, $key) {
        return $this->redis->hGet($name, $key);
    }

    /*
     * 获取hash 格式
     */

    public function hLen($name) {
        return $this->redis->hLen($name);
    }

    /*
     * 删除hash值
     */

    public function hDel($name, $key) {
        $this->redis->hDel($name, $key);
    }

    /*
     * 返回hash所有
     */

    public function hGetAll($name) {
        return $this->redis->hGetAll($name);
    }

    /*
     * 添加list左边值
     */

    public function lPush($key, $value) {
        $this->redis->lPush($key, $value);
    }

    /*
     * 添加list右边值
     */

    public function rPush($key, $value) {
        $this->redis->rPush($key, $value);
    }

    /*
     * 获取list个数
     */

    public function lSize($key) {
        return $this->redis->lSize($key);
    }

    /*
     * 获取list
     */

    public function lRange($key, $start = 0, $end = -1) {
        return $this->redis->lRange($key, $start, $end);
    }

    /*
     * 删除list
     */

    public function lRem($key) {
        $list = $this->lRange($key);
        foreach ($list as $v) {
            $this->redis->lRem($key, $v, 1);
        }
    }

    /*
     * 删除list单个值
     */

    public function lOneRem($key, $v) {
        $this->redis->lRem($key, $v, 1);
    }

}