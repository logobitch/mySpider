<?php
/**
 * File: ReadUrl.php
 * User: zhoucong@yongche.com
 * Date: 16/12/20
 * Time: 上午10:16
 */
namespace SP\Common;

class ReadUrl{
    public $spiderType = 'curl';
    private $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36 OPR/41.0.2353.69';

    public function readFromUrl($url, $ext = array(), $type='') {
        $this->spiderType = $type;
        if ($this->spiderType == '') {
            $this->spiderType = $this->_getSpiderType();
        }
        switch ($this->spiderType) {
            case 'curl':
                $content = $this->_spiderByCurl($url, $ext);
                break;
            case 'fsock':
                $content = $this->_spiderByFsock($url);
                break;
            case 'fopen':
                $content = $this->_spiderByFopen($url);
                break;
            case 'curl_multi':
                if(is_array($url) && !empty($url)) {
                    $content = $this->_spiderByCurlMulti($url, $ext);
                } else {
                    $content = '';
                }
                break;
            default:
                $content = '';
                break;
        }
        return $content;
    }

    private function _spiderByCurlMulti($urls = array(), $ext)
    {
        $mh = curl_multi_init();
        $max = 3;
        for ($i = 0; $i < $max; $i++) {
            $ch = curl_init();
            @curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_URL, $urls[$i]);
            if (!empty($ext['cookie'])) {
                $cookies = array();
                foreach ($ext['cookie'] as $key => $value) {
                    $cookies[] = "$key=$value";
                }
                $cookie = implode('; ', $cookies);
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);

            $requestMap[$i] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        $res = array();
        do {
            while (($cme = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($cme != CURLM_OK) {
                break;
            }
            while ($done = curl_multi_info_read($mh)) {
                $info = curl_getinfo($done['handle']);
                $tmp_result = curl_multi_getcontent($done['handle']);
                $error = curl_error($done['handle']);

                $res[] = $tmp_result;

                curl_multi_remove_handle($mh, $done['handle']);
            }
            if ($active) {
                curl_multi_select($mh, 10);
            }
        } while ($active);

        curl_multi_close($mh);
        return $res;
    }

    private function _spiderByFopen($url) {
        $content = file_get_contents($url);
        return $content;
    }

    private function _spiderByFsock($url) {
        if (!preg_match('//', $url)) {
            Error::triggerError('fsockopen url must has http scheme');
        }
        $parts = parse_url($url);
        $host = $parts['host'];
        $port = 80;
        if ($parts['scheme'] == 'https') {
            $port = 443;
        }

        $path = '/';
        if (isset($parts['path'])) {
            $path = $parts['path'];
            if (!empty($parts['query'])) {
                $path .= '?' . $parts['query'];
            }
        }
        $request = "GET " . $path . " HTTP/1.0\r\n";

        $request .= "Host: " . $host . "\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "Connection: keep-alive\r\n";
        $request .= "User-Agent: {$this->agent}\r\n\r\n";

        if ($parts['scheme'] == 'https') {
            $fp = fsockopen('ssl://' . $host, $port, $errno, $errstr, 30);
        } else {
            $fp = fsockopen($host, $port, $errno, $errstr, 30);
        }
        if ($fp === false) {
            Error::triggerError('fsockopen打开网络资源出错,msg:' . $errstr, $errno);
        }
        stream_set_blocking($fp, true);
        stream_set_timeout($fp, 1);
        fwrite($fp, $request);
        $content = '';
        while (!feof($fp)) {
            if (!isset($step)) {
                $step = 4096;
                //为什么是4096
            }
            $line = fgets($fp, $step + 1);
            if (strpos($line, 'Location:') === 0) {
                //为301，302跳转 跟进
                $url = substr(trim($line), 10);
                return $this->readFromUrl($url);
            }
            if (isset($size) && isset($length)) {
                $content .= $line;
                if ($length != -1) {
                    $size += strlen($line);
                    $step = min(4096, $length - $size);
                    if ($step <= 0) {
                        break;
                    }
                }
            }
            if (substr($line, 0, 15) == 'Content-Length:') {
                $length = intval(substr($line, 15));
            }
            if ($line == "\r\n" && !isset($size)) {
                $size = 0;
                if (!isset($length)) {
                    $length = -1;
                }
            }
        }
        fclose($fp);
        return $content;
    }

    private function _spiderByCurl($url, $ext) {
        $ch = curl_init();
        @curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($ext['cookie'])) {
            $cookies = array();
            foreach ($ext['cookie'] as $key => $value) {
                $cookies[] = "$key=$value";
            }
            $cookie = implode('; ', $cookies);
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    private function _getSpiderType()
    {
        if (function_exists('curl_init')) {
            return 'curl';
        } elseif (function_exists('fsockopen')) {
            return 'fsock';
        } elseif (ini_get('allow_url_fopen') == 1) {
            return 'fopen';
        } elseif (isset($GLOBALS['wget']) && function_exists('system')) {
            return 'wget';
        } else {
            return false;
        }
    }
}