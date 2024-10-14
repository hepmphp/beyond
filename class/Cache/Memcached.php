<?php

namespace helpers\Cache;

class Memcached extends Cache{
    public $cache_instance = null;
    public $default_config = array(
        'host'=>'127.0.0.1',
        'port'=>'11211',
    );
    public function __construct($config){
        $this->cache_instance = new \Memcached();
        if(isset($config['host']))
        {
            $config = array($config);
        }elseif(empty($configs)){
            $config = array($this->default_config);//ÔØÈëÄ¬ÈÏÉèÖÃ
        }
        $this->cache_instance->addServers($config);
    }
    public function set($id,$data,$ttl){
        return $this->cache_instance->set($this->build_cache_key($id),$data,$ttl);
    }
    public function get($id){
        return $this->cache_instance->get($this->build_cache_key($id));
    }
    public function sets($datas,$ttl){
        return $this->cache_instance->setMulti($datas,$ttl);
    }
    public function gets($ids){
        return $this->cache_instance->getMulti($ids);
    }
    public function delete($id){
        return $this->cache_instance->delete($this->build_cache_key($id));
    }
    public function clean(){
        return $this->cache_instance->flush();
    }
    public function cache_info(){
        return $this->cache_instance->getStats();
    }
}