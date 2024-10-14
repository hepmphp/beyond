<?php

namespace helpers\Cache;

class Memcache extends Cache{
    public $cache_instance = null;
    public $default_config = array(
            'host'=>'127.0.0.1',
            'port'=>'11211',
    );
    public function __construct($configs=array()){
        $this->cache_instance = new \Memcache();
        if(isset($configs['host']))
        {
            $configs = array($configs);
        }elseif(empty($configs)){
            $configs = array($this->default_config);//ÔØÈëÄ¬ÈÏÉèÖÃ
        }
        foreach($configs as $config){
            if(empty($config)) continue;
            $this->cache_instance->addServer($config['host'],$config['port']);
        }
    }
    public function set($id,$data,$ttl=86400){
        return $this->cache_instance->set($this->build_cache_key($id),$data,0,$ttl);
    }
    public function get($id){
        return $this->cache_instance->get($this->build_cache_key($id));
    }
    public function sets($datas,$ttl=86400){
        foreach($datas as $id=>$data){
            $this->set($id,$data,$ttl);
        }
        return true;
    }
    public function gets($ids){
        $datas = array();
        foreach($ids as $id){
            $datas[$id] = $this->get($id);
        }
        return $datas;
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