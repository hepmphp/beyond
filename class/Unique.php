<?php 
namespace helpers;
class Unique {
    /**
     * 返回全局唯一标识符GUID
     *
     * @access public
     * @static
     *
     * @return string
     */
    public static function guid() {
        mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
        $charId = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid   = substr($charId, 0, 8) . $hyphen
            . substr($charId, 8, 4) . $hyphen
            . substr($charId, 12, 4) . $hyphen
            . substr($charId, 16, 4) . $hyphen
            . substr($charId, 20, 12);
        return $uuid;
    }
	
	public static function order_no(){
		return date('YmdHis').mt_rand(10000,99999);
	}
	
	public static function microtime(){
		return microtime(true);
	}
	
	
	public static function salt(){
        $str = '';
        for ($i = 0; $i < 14; $i++) {
            $str .= chr(rand(48,126));  //<48有双引号和单引号
        }
        return $str;
    }

     /*
     * 产生随机字符串
     * 产生一个指定长度的随机字符串,并返回给用户
     * @access public
     * @param int $len 产生字符串的位数
     * @return string
     */

    static function genRandomString($len = 6) {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        shuffle($chars);    // 将数组打乱
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

}
 
