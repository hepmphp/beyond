<?php
namespace Hepm\Helpers;
class Log
{
    public static $log_path = './log/';
    /**
     * 设置日志路径
     */
    public static function dir($dir_path){
        self::$log_path = $dir_path;
    }
    /***
     *
     * @param $message  消息
     * @param $filename 文件
     * @param $type     日志类型 common普通日志 pay支付日志  login登录日志 user用户日志 等等
     */
    public static function write($message,$filename,$type='common'){
        $log_dir = "%s/%s/%s";//日志路径 日志类型 年 月 日
        $dir = sprintf($log_dir,self::$log_path,$type,date('Y/m/d/'));
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        $log_file = $dir.$filename;

        $message = date('Y-m-d H:i:s')."\t";
        $message .= $message.PHP_EOL;
        error_log($message,3, $log_file);//接口请求写入日志
    }

}