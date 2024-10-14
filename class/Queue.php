<?php
namespace helpers;
class Queue{
    public static $queue_list = array();
    public static function shift(){
        array_shift(self::$queue_list);
    }

    public static function push($item){
        array_push(self::$queue_list,$item);
    }
}