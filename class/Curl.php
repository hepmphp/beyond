<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xiaoming
 * Date: 17-5-4
 * Time: 下午10:46
 * To change this template use File | Settings | File Templates.
 */

namespace helpers;

/***
 * curl封装
 * Class Curl
 * @package helpers
 */
class Curl {

    public function upload_image($ad_account,$file){
        $header = explode("\n",$ad_account['login_cookie']);
        $url = "https://ad.toutiao.com/tools/upload_image/";
        if (class_exists('CURLFile')) {
            $form_data = array(
                'Filedata' => new CURLFile(realpath($file)),
                'file_type'=>pathinfo($file)['extension'],
            );
        } else {
            $form_data = array(
                'file' => '@' . realpath($file),
                'file_type'=>pathinfo($file)['extension'],
            );
        }
        $delimiter = 'WebKitFormBoundaryDdpcP63IoiLWNtDl';
        $header[] = "Content-type: multipart/form-data; boundary=----{$delimiter}";

        $res = $this->upload($url,$form_data,$header);
    }

    public function build_form_binary($form_data,$delimiter){
        $form_data_str = '';
        // 表单数据
        foreach ($form_data as $name => $content) {
            if(!is_array($content) && empty($content)&& !is_numeric($content)){
                $content = 'false';
            }
            $form_data_str .= "------" . $delimiter . '\r\n';
            $form_data_str .= 'Content-Disposition: form-data; name="' . $name . '"';
            $form_data_str .= '\r\n\r\n';
            $form_data_str .= $content;
            $form_data_str .= '\r\n';
        }
        $form_data_str .= '------'.$delimiter.'--\r\n';
        return $form_data_str;
    }

    /***
     * @param $url
     * @param array $header_options
     * @return mixed
     */
    function get($url,array $header_options = array())
    {
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1, //返回原生的（Raw）输出
//            CURLOPT_HEADER => 0,
//            CURLOPT_TIMEOUT => 120, //超时时间
//            CURLOPT_FOLLOWLOCATION => 1, //是否允许被抓取的链接跳转
            CURLOPT_ENCODING=>'gzip,deflate',
            CURLOPT_HTTPHEADER => $header_options,
        );
        if (strpos($url,"https")!=false) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false; // 对认证证书来源的检查
        }
        curl_setopt_array($ch, $curl_options);
        $res = curl_exec($ch);
        $data = json_decode($res,true);
        if(json_last_error() != JSON_ERROR_NONE){
            $data = $res;
        }
        curl_close($ch);
        return $data;
    }
    /**
     * post 请求
     * @param $url 请求url
     * @param array $param  post参数
     * @param array $header 头部信息
     * @param bool $login   是否登陆
     * @param int $ssl      启用ssl
     * @param int $log      是否记录日志
     * @param string $format返回数据格式
     * @return mixed
     */
    function post($url, array $param = array(), array $header = array())
    {
        $ch = curl_init();
        $post_param = array();
        if (is_array($param)) {
            $post_param = http_build_query($param);
        } else if (is_string($param)) { //json字符串
            $post_param = $param;
        }

        $header_options =  $header;
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1, //返回原生的（Raw）输出
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 120, //超时时间
            CURLOPT_FOLLOWLOCATION => 1, //是否允许被抓取的链接跳转
            CURLOPT_HTTPHEADER => $header_options,
            CURLOPT_POST => 1, //POST
            CURLOPT_POSTFIELDS => $post_param, //post数据
            CURLOPT_ENCODING=>'gzip,deflate'
        );

        //debug 1
//        curl_setopt($ch,CURLINFO_HEADER_OUT,1);
//        curl_setopt($ch,CURLOPT_HEADER,1);
        //debug 2 详细的请求过程
//        curl_setopt($ch,CURLOPT_VERBOSE,true);
//        curl_setopt($ch,CURLINFO_HEADER_OUT,0);
//        curl_setopt($ch,CURLOPT_HEADER,0);
//        curl_setopt($ch,CURLOPT_VERBOSE,true);
//        $fp = fopen('php://temp', 'rw+');
//        curl_setopt($ch,CURLOPT_STDERR,$fp);

        if (strpos($url,"https")!==false) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false; // 对认证证书来源的检查
        }
        curl_setopt_array($ch, $curl_options);
        $res = curl_exec($ch);

        // $debug_info = rewind($fp) ? stream_get_contents($fp):"";
        //$debug_info = curl_getinfo($ch);
        //  print_r($debug_info);
        $data = json_decode($res, true);
        if(json_last_error() != JSON_ERROR_NONE){
            $data = $res;
        }
        curl_close($ch);
        return $data;
    }
    /**
     * 上传 请求
     * @param $url 请求url
     * @param array $param  post参数
     * @param array $header 头部信息
     * @param bool $login   是否登陆
     * @param int $ssl      启用ssl
     * @param int $log      是否记录日志
     * @param string $format返回数据格式
     * @return mixed
     */
    function upload($url, array $post_param = array(), array $header = array())
    {
        $ch = curl_init();
        $header_options =  $header;
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1, //返回原生的（Raw）输出
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 120, //超时时间
            CURLOPT_FOLLOWLOCATION => 1, //是否允许被抓取的链接跳转
            CURLOPT_HTTPHEADER => $header_options,
            CURLOPT_POST => 1, //POST
            CURLOPT_POSTFIELDS => $post_param, //post数据
            CURLOPT_ENCODING=>'gzip,deflate'
        );
        if (strpos($url,"https")!==false) {
            $curl_options[CURLOPT_SSL_VERIFYPEER] = false; // 对认证证书来源的检查
        }
        curl_setopt_array($ch, $curl_options);
        $res = curl_exec($ch);
        $data = json_decode($res, true);
        if(json_last_error() != JSON_ERROR_NONE){
            $data = $res;
        }
        curl_close($ch);
        return $data;
    }
}
