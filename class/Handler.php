<?php
/**
 * User: fish
 * Date: 2016-08-02 00:22
 * Handler.php
 */

namespace helpers;
/**
 * 错误以及异常处理
set_error_handler(array('helpers\Handler','error_handler'));
//set_exception_handler('_exception_handler');
register_shutdown_function(array('helpers\Handler','shutdown_handler'));
 *
 * Class Handler
 * @package helpers
 */
class Handler {
    const LOG_PATH = '/log/error_log.log';
    const LAST_ERROR_lOG = '/log/last_error.html';
    public static $levels = array(
        E_ERROR  			=>	'Error',
        E_WARNING			=>	'Warning',
        E_PARSE				=>	'Parsing Error',
        E_NOTICE			=>	'Notice',
        E_CORE_ERROR		=>	'Core Error',
        E_CORE_WARNING		=>	'Core Warning',
        E_COMPILE_ERROR		=>	'Compile Error',
        E_COMPILE_WARNING	=>	'Compile Warning',
        E_USER_ERROR		=>	'User Error',
        E_USER_WARNING		=>	'User Warning',
        E_USER_NOTICE		=>	'User Notice',
        E_STRICT			=>	'Runtime Notice'
    );

    /**
     * 错误处理函数
     * @param $errno
     * @param $errstr
     * @param string $errfile
     * @param string $errline
     * @param array $errcontext
     */
    public static function error_handler($errno,$errstr,$errfile='',$errline='',$errcontext=array()){
        self::mk_log_dir();
        $errcode = self::$levels[$errno];
        $log_message = "错误代码:[%s],错误信息:[%s],文件:[%s],行号:[%d],地址:[%s],来源:[%s]";
        $url     = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';

        $log_message_format = sprintf($log_message,$errcode,$errstr,$errfile,$errline,$url,$referer);
        error_log($log_message_format.PHP_EOL,3,BASE_PATH.self::LOG_PATH);
    }

    /**
     *php错误邮件告警
     */
    public static function shutdown_handler(){
        self::mk_log_dir();
        $last_error = error_get_last();
        if (isset($last_error['type']) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
        {
            $time = date("Y-m-d H:i:s");
            $email_msg = 'PHP Fatal Error '.$time;
            $email_content[] = "File:".$_SERVER['PHP_SELF'];
            $email_content[] = "Url:http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $email_content[] = "Error:".var_export($last_error, true);
            $email_content[] = "Server_IP:".$_SERVER["SERVER_ADDR"];
            $email_content[] = "User_IP:".$_SERVER['REMOTE_ADDR'];
            $email_content[] = "Time:".$time;
            $email_content_msg = "<pre>".$email_msg."<br/>".implode("<br/>",$email_content);
            error_log($email_content_msg."<hr>",3,BASE_PATH.self::LAST_ERROR_lOG);
        }
    }

    public static function mk_log_dir(){
        if(!is_dir(BASE_PATH.'/log')){
            mkdir(BASE_PATH.'/log',0755,true);
        }
    }

}
