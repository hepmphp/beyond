<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xiaoming
 * Date: 16-12-30
 * Time: 下午11:09
 * To change this template use File | Settings | File Templates.
 */

namespace helpers;


class Url {

    static function create($url){
       return "/index.php/".$url;
    }

    /**
     * URL重定向
     * @param string $url 重定向的URL地址
     * @param integer $time 重定向的等待时间（秒）
     * @param string $msg 重定向前的提示信息
     * @return void
     */
   static  function redirect($url, $time=0, $msg='') {
       $url = Url::create($url);
        //多行URL地址支持
        $url        = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }

}