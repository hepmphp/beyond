<?php

namespace helpers\Cache;

class File extends Cache{
    public $path = '';
    public function __construct($config=array()){
        $this->path = isset($config['cache_path'])?$config['cache_path']:BASE_PATH.'/cache/file/';
        if(!is_dir($this->path)){
            mkdir($this->path,0755,true);
        }
    }
    public function set($id,$data,$ttl=86400){
        $contents = array(
            'time'=>time(),
            'ttl'=>$ttl,
            'data'=>$data,
        );
        $cache_key = $this->build_cache_key($id);
        $result = file_put_contents($this->path.$cache_key,serialize($contents));
        return $result;
    }
    public function get($id)
    {
        $cache_key = $this->build_cache_key($id);
        $data = file_get_contents($this->path.$cache_key);
        $data = unserialize($data);
        if(time()>$data['time']+$data['ttl']){
            unlink($this->path.$cache_key);
            return false;
        }
        return $data['data'];
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
        return unlink($this->path.$this->build_cache_key($id));
    }
    public function clean(){
        foreach(glob($this->path.'*') as $file_name){
            unlink($file_name);
        }
    }

    public function cache_info(){
        return glob($this->path.'*');
    }


}