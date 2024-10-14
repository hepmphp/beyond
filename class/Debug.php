<?php
/**
 * User: fish
 * Date: 2016-08-02 00:44
 * Debug.php
 */



class Debug {
     public static function last_log(){
         $last_log = array(
//              '$_GET'=>print_r($_GET,true),
//              '$_POST'=> print_r($_POST,true),
//              '$_SERVER'=>print_r($_SERVER,true),
//              '$_COOKIE'=> print_r($_COOKIE,true),
//              '$_FILES' =>print_r($_FILES,true),
//              '$_ENV'=>print_r($_ENV,true),
              //'$_SESSION'=>print_r($_SESSION,true),
              '$GLOBALS'=>print_r($GLOBALS,true)
         );
         return $last_log;
     }

    /**
     * 打印调用
     */
    static function print_stack_trace()
    {
        $e = new \Exception();
        print_r(str_replace('/path/to/code/', '', $e->getTraceAsString()));
    }
    /*
     * 打印被包含的文件
     */
    static function print_include_files(){
        print_r(get_included_files());
    }

}