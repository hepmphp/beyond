<?php
/**
 * User: fish
 * Date: 2016-08-03 23:45
 * Input.php
 */
//Input::get Input::post Input::get_post Input::input Input::filter
namespace helpers;

class Input {
    public static function trim(&$data){
        $data = array_map('trim',$data);
    }

    public static function xss_clean(&$data, array $preserve_key = array()) {
        if (!is_array($data) || empty($data)) {
            return;
        }
        array_walk($data,
            function(&$value, $key) use($preserve_key) {
                if (is_array($value)) {
                    return xss_clean($value, $preserve_key);
                } else {
                    if (in_array($key, $preserve_key) === false) {
                        $value = htmlspecialchars($value, ENT_COMPAT ,'GB2312');
                    }
                }
            });
    }

    /**
     * get请求
     * @param $index
     * @param $xss_clearn
     */
    public static function get($index,$default='',$filter=''){
        $data = isset($_GET[$index])?$_GET[$index]:'';
        if(empty($data) && $default){
            $data = $default;
        }
        if($filter){
            $data = Input::filter($data,$filter);
        }
        return $data;
    }

    /**
     * post请求
     * @param $index
     * @param $xss_clearn
     */
    public static function post($index,$default='',$filter=''){
        $data = isset($_POST[$index])?$_POST[$index]:'';
        if(empty($data) && $default){
            $data = $default;
        }
        if($filter){
            $data = Input::filter($data,$filter);
        }
        return $data;
    }

    public static function get_post($index='',$default='',$filter=''){
        $input = array_merge($_GET,$_POST);
        $data = isset($input[$index])?$input[$index]:'';
        if(empty($data) && $default){
            $data = $default;
        }
        if($filter){
            $data = Input::filter($data,$filter);
        }
        return $data;
    }

    /***
     * php://input获取
     * @param string $index
     * @param string $default
     * @param string $filter
     * @return array|mixed|null|string
     */
    public static function input($index='',$default='',$filter=''){
        parse_str(file_get_contents('php://input'), $input);
        $data = isset($input[$index])?$input[$index]:'';
        if(empty($data) && $default){
            $data = $default;
        }
        if($filter){
            $data = Input::filter($data,$filter);
        }
        return $data;
    }

    /**
     * 过滤
     * @param $data
     * @param string $filter
     * @return array|mixed|null
     */
    public static function filter($data,$filter=''){
      //  $data       =	$input[$name];
        $filters    =   !empty($filter)?$filter:'htmlspecialchars';
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return	 isset($default)?$default:NULL;
                    }
                }
            }
        }
        return $data;
    }
    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @return mixed
     */
    static function get_client_ip($type = 0) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    static function is_post(){
        return strtolower($_SERVER['REQUEST_METHOD']) =='post';
    }

}
