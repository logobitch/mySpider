<?php
/**
 * File: Error.php
 * User: zhoucong@yongche.com
 * Date: 16/12/20
 * Time: 上午10:21
 */
namespace SP\Common;

class Error{
    public static function triggerError($msg='发生错误!', $code=500){
        exit($msg);
    }

    public static function logWrite($msg, $logFile='error.log') {
        $msg = '['.date('Y-m-d H:i:s', time()) .'] '.$msg;
        error_log($msg."\n\n", 3, '/var/log/nginx/spider/'.$logFile.date('Y-m-d'));
    }
}