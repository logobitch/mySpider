<?php
/**
 * File: Error.php
 * User: zhoucong@yongche.com
 * Date: 16/12/20
 * Time: 上午10:21
 */
namespace SP\Common;

class Error{
    /**
     * @description 自定义返回错误，直接退出
     * code 666表示调试，其他错误类型请自定义错误码
     * @param string $msg
     * @param int $code
     */
    public static function triggerError($msg='发生错误!', $code=666){
        $ret = array(
            'code'  => $code,
            'msg'   => $msg,
        );
        var_export($ret);
        die;
    }

    public static function logWrite($msg, $logFile='error.log') {
        $msg = '['.date('Y-m-d H:i:s', time()) .'] '.$msg;
        error_log($msg."\n\n", 3, '/var/log/nginx/spider/'.$logFile.date('Y-m-d'));
    }

    public static function spiderDebug($content) {
        echo $content;
        die;
    }
}