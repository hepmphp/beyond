<?php
/**
 * Created by JetBrains PhpStorm.
 * User: T171
 * Date: 16-10-10
 * Time: 上午10:22
 * To change this template use File | Settings | File Templates.
 */

namespace helpers\Security;

/**
 * DES加密类
 *
 * 本类用于实现des算法的加密及解密
 *
 * 调用方法
 *     Des::setKey('key值');  // key只能是八位
 *     $xx = Des::encrypt('xxxxx');// 解密
 *     $aa = Des::decrypt($xx);    // 加密
 */
class Des {
    static $key='1234abcd';
    //key长度8例如:1234abcd
    static function setKey($key) {
        self::$key = $key;
    }
    static function  encrypt($encrypt) {
        $encrypt = self::pkcs5_pad($encrypt);
        $passcrypt = @mcrypt_encrypt(MCRYPT_DES, self::$key, $encrypt, MCRYPT_MODE_CBC);
        return strtoupper(bin2hex($passcrypt));
    }
    static function decrypt($decrypt) {
        $decoded = pack("H*", $decrypt);
        $decrypted = @mcrypt_decrypt(MCRYPT_DES, self::$key, $decoded, MCRYPT_MODE_CBC);
        return self::pkcs5_unpad($decrypted);
    }
    static  function pkcs5_unpad($text) {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) {
            return $text;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return $text;
        }
        return substr($text, 0, -1 * $pad);
    }
    static function pkcs5_pad($text) {
        $len = strlen($text);
        $mod = $len % 8;
        $pad = 8 - $mod;
        return $text.str_repeat(chr($pad),$pad);
    }
}