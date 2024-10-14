<?php


namespace helpers\Cache;


abstract class Cache {
    public $cache_prefix = 'CACHE_';
    public function set($id,$data,$ttl){}
    public function get($id){}
    public function sets($datas,$ttl){}
    public function gets($ids){}
    public function delete($id){}
    public function clean(){}
    public function cache_info(){}
    /**
     * Éú³É»º´ækey
     * @param $key   ËÑË÷µÄ²ÎÊý
     * @return string »º´æ×Ö·û´®
     */
    public function build_cache_key($key){
        if(is_string($key))
        {
            $key = ctype_alnum($key)&&mb_strlen($key)<32?$key:md5($key);
        }else{
            $key = md5(json_encode($key));
        }
        return $this->cache_prefix.$key;
    }


}