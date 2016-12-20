<?php
namespace Spider;

use App\Model\SpiderModel;

Class Spider
{
    private $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36 OPR/41.0.2353.69';

    private $sources_path = 'config/spiders/';
    private $sources;
    private $source_list = array();

    public $spiderType = 'curl';

    public $contentList = '';
    public $linkList = array();

    public function __construct()
    {
        $this->sources_path = __DIR__ . '/../../' . $this->sources_path;
    }

    public function daemon() {
        $sourceList = $this->getSourceList();
        if(empty($sourceList)) {
            $this->_triggerError('配置信息为空! 请检查');
        }
        foreach($sourceList as $source) {
            $listContent = $this->getContentList($source);

            $links = $this->getLinkList($source, $listContent);

            $items = $this->getItems($source, $links);

//            var_dump($items);
            $this->saveSpiderItem($items);
        }
    }

    /**
     * @description 获取界面内容
     * @param array $source
     * @param string $type
     * @return string
     */
    public function getContentList($source=array(), $type='')
    {
        if (!isset($source['list_url']) || empty($source['list_url'])) {
            echo "this is not correct!";
            return 'this is not correct!';
        }
        $listContent = trim($this->_readFromUrl($source['list_url'], $source['ext']), $type);

        $listStart = isset($source['list_start']) ? $source['list_start'] : '';
        $listEnd = isset($source['list_end']) ? $source['list_end'] : '';

        if(! preg_match("/$listStart(.*)$listEnd/s", $listContent, $match)) {
            echo $listContent;
            return 'CAN NOT PREG ANYTHING';
        }
        $this->contentList = trim($match[1]);
        return $this->contentList;
    }

    /**
     * @description 获取抓取链接地址
     * @param array $source
     * @param string $list
     * @return array
     */
    public function getLinkList($source=array(), $list='') {
        if($list == '') {
            return array();
        }
        $listArr = explode($source['list_separator'], $list);
        foreach($listArr as $list) {
            $links = $this->_parseLink($list);

            foreach($links as $link => $title) {
                $link = $this->_formatUrl($source['list_url'], $link);

                if(isset($source['url_function']) && function_exists($source['url_function'])) {
                    $link = $source['url_function']($link);
                }
                if(! $link) {
                    continue;
                }
                $this->linkList[] = [
                    'title' => $title,
                    'link'  => $link
                ];
            }
        }
        $this->linkList = array_reverse($this->linkList);
        if(isset($source['list_shuffle'])) {
            shuffle($this->linkList);
        }
        return $this->linkList;
    }

    /**
     * @description 从链接中获取item数据
     * @param $source
     * @param $links
     * @param string $type
     * @return array
     */
    public function getItems($source, $links, $type='curl') {
        //从配置中读取信息
        $rules = array();
        foreach($source as $key => $value) {
            if(preg_match('/^output_(\w+)/', $key, $match)) {
                $rules[$match[1]] = $value;
            }
        }

        $items = array();
        //从规则中抓取相关信息
        foreach($links as $link) {
            $item = $this->getItem($source, $link, $type);

            //处理正文内容
            if(isset($item['content']) && !empty($item['content'])) {
                $item['content'] = $this->_clearContentAttribute($item['content']);
            }
            $item = array_merge($item, $rules);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @description 保存界面数据
     * @param $items
     */
    public function saveSpiderItem($items) {
        foreach($items as $item) {
            $ret = SpiderModel::create($item);
            if(! $ret) {
                $msg = 'spider data into database error!!'.json_encode($item);
                $this->_logWrite($msg);
            }
        }
    }

    /**
     * @description 从文件读取抓取配置
     * @return array
     */
    public function getSourceList() {
        if (!file_exists($this->sources_path)) {
            $this->_triggerError('抓取文件目录不存在');
        }
        $sourcesList = scandir($this->sources_path);
        $sources = array();
        foreach ($sourcesList as $source) {
            if (preg_match('/^,.*\.php/', $source)) {
                $tmp = require $this->sources_path . $source;
                if (!empty($tmp)) {
                    $sources[] = $tmp;
                }
            }
        }
        $this->source_list = $sources;
        return $this->source_list;
    }

    private function _clearContentAttribute($html) {
        $html = preg_replace("/<script>.*<\/script>/is", '', $html);
        $html = strip_tags($html, '<br><p><img><table><td><tr>');
        $html = preg_replace("/<p[^>]*>/i", '<p>', $html);
        $html = preg_replace("/<\/p>/i", '</p>', $html);
        $html = preg_replace("/<!--.*-->/", '', $html);
        $html = preg_replace("/<p>\s*<\/p>/", '', $html);
        return $html;
    }

    private function getItem($source, $link, $type){
        $content = $this->_readFromUrl($link['link'], $type);
        $content = str_replace("\r", '', $content);

        $output = array();
        for($i=1; $i<20; $i++) {
            if(!isset($source['start'.$i]) || !isset($source['end'.$i]) || empty($source['start'.$i]) || empty($source['end'.$i])) {
                break;
            }
            $field_start = $source['start'.$i];
            $field_end = $source['end'.$i];
            $field = $source['field'.$i];

            if(! preg_match("#$field_start(.*?)$field_end#s", $content, $match)) {
                $msg = "其中的  >>{$field}<<  字段抓取失败!";
                $this->_logWrite($msg);
            }

            $output[$field] = isset($match[1]) ? $match[1] : '';
        }
        return $output;
    }

    private function _parseLink($list) {
        $html = strip_tags($list, '<a><title><link>');
        preg_match_all("/<\s*a.*?href\s*=(.+?)(\s+.*?)?>(.*?)<\s*\/a\s*>/isx", $html, $matchs);
        preg_match_all("/<title>(.+?)<\/title>\s*<link>(.+?)<\/link>/isx", $html, $matchs2);
        $links = array();

        foreach($matchs[1] as $key => $link) {
            $link = str_replace('\'', '', $link);
            $link = str_replace('"', '', $link);
            $links[$link] = '';
        }
        foreach($matchs2[2] as $key => $link) {
            $link = str_replace('\'', '', $link);
            $link = str_replace('"', '', $link);
            $title = $matchs2[1][$key];
            $links[$link] = $title;
        }
        return $links;
    }

    private function _formatUrl($baseUrl, $targetUrl) {
        if($targetUrl == '' || $baseUrl == '') {
            return false;
        }
        $urlInfo = parse_url($targetUrl);
        if(isset($urlInfo['scheme']) && ($urlInfo['scheme'] == 'http' || $urlInfo['scheme'] == 'https')) {
            return $targetUrl;
        }
        $urlInfo = parse_url($baseUrl);
        if(substr($targetUrl, 0, 1) == '/') {
            return $urlInfo['scheme'].'://'.$urlInfo['host'].$targetUrl;
        }

        if(!isset($urlInfo['path']) || $urlInfo['path'] == '/') {
            return $urlInfo['scheme'].'://'.$urlInfo['host'].'/'.$targetUrl;
        }
        $dirName = dirname($urlInfo['path']);
        $dirName = str_replace('\\', '', $dirName);
        return $urlInfo['scheme'].'://'.$urlInfo['host'].$dirName.'/'.$targetUrl;
    }

    private function _readFromUrl($url, $ext = array(), $type='')
    {
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

    private function _spiderByFsock($url)
    {
        if (!preg_match('//', $url)) {
            $this->_triggerError('fsockopen url must has http scheme');
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
            $this->_triggerError('fsockopen打开网络资源出错,msg:' . $errstr . ';code:' . $errno);
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
                return $this->_readFromUrl($url);
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

    private function _spiderByCurl($url, $ext)
    {
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

    private function _triggerError($msg = '发生错误！')
    {
        exit($msg);
    }

    private function _logWrite($msg, $logFile='insert_error.log') {
        $msg = date('Y-m-d H:i:s', time()).$msg;
        error_log($msg."\n\n", 3, '/var/log/nginx/spider/'.$logFile.date('Y-m-d'));
    }
}
