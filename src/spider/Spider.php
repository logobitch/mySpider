<?php
namespace SP\Spider;

use App\Model\Item;
use App\Model\SpideredModel;
use SP\Common\Error;
use SP\Common\ReadUrl;

Class Spider
{
    private $sources_path = 'config/spiders/';
    private $source_list = array();

    public $spiderType = 'curl';

    public $contentList = '';
    public $linkList = array();

    public function __construct()
    {
        $this->sources_path = __DIR__ . '/../../' . $this->sources_path;
    }

    public function daemon()
    {
        $sourceList = $this->getSourceList();
        if (empty($sourceList)) {
            Error::triggerError('配置信息为空! 请检查');
        }
        foreach ($sourceList as $source) {
            $listContent = $this->getContentList($source);
            if (!$listContent) {
                continue;
            }

            $links = $this->getLinkList($source, $listContent);

            $items = $this->getItems($source, $links);

            $this->saveSpiderItem($items);
        }
    }

    /**
     * @description 获取界面内容
     * @param array $source
     * @param string $type
     * @return string
     */
    public function getContentList($source = array(), $type = '')
    {
        if (!isset($source['list_url']) || empty($source['list_url'])) {
            $msg = 'spider list url is empty!' . json_encode($source);
            Error::logWrite($msg);
            return false;
        }
        $listContent = trim($this->_readFromUrl($source['list_url'], $source['ext']), $type);

        $listStart = isset($source['list_start']) ? $source['list_start'] : '';
        $listEnd = isset($source['list_end']) ? $source['list_end'] : '';

        if (!preg_match("/$listStart(.*)$listEnd/s", $listContent, $match)) {
            $msg = "can no preg anything from list !" . json_encode($source);
            Error::logWrite($msg);
            return false;
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
    public function getLinkList($source = array(), $list = '')
    {
        if ($list == '') {
            return array();
        }
        $listArr = explode($source['list_separator'], $list);
        foreach ($listArr as $list) {
            $links = $this->_parseLink($list);

            foreach ($links as $link => $title) {
                $link = $this->_formatUrl($source['list_url'], $link);

                if (isset($source['url_function']) && function_exists($source['url_function'])) {
                    $link = $source['url_function']($link);
                }
                if (!$link) {
                    continue;
                }
                $this->linkList[] = [
                    'title' => $title,
                    'link' => $link
                ];
            }
        }
        $this->linkList = array_reverse($this->linkList);
        if (isset($source['list_shuffle'])) {
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
    public function getItems($source, $links, $type = 'curl')
    {
        //从配置中读取信息
        $rules = array();
        foreach ($source as $key => $value) {
            if (preg_match('/^output_(\w+)/', $key, $match)) {
                $rules[$match[1]] = $value;
            }
        }

        $items = array();
        //从规则中抓取相关信息
        foreach ($links as $link) {
            if($this->hasSpidered($link['link'])) {
                $msg = "this url has been spidered::" . $link['link'] .'skip!!!';
                Error::logWrite($msg);
                continue;
            }

            $item = $this->getItem($source, $link, $type);

            //处理正文内容
            if (isset($item['content']) && !empty($item['content'])) {
                $item['content'] = $this->_clearContentAttribute($item['content']);
            }
            $item = array_merge($item, $rules);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @description 保存界面数据,记录抓取历史
     * @param $items
     */
    public function saveSpiderItem($items)
    {
        foreach ($items as $item) {
            //如果没有抓取到title,则认为本次的抓取不成功
            if(!isset($item['title']) || empty($item['title'])) {
                $this->saveSpiderHistory($item['url'], -1);
                $msg = "文章标题抓取失败!认定该次抓取不成功!". json_encode($item);
                Error::logWrite($msg);
                continue;
            }

            $itemModel = new Item();
            $spiderId = $itemModel->createNewItem($item);
            if (!$spiderId) {
                //插入记录
                $msg = '抓取到的内容存储失败!'.json_encode($item);
                Error::logWrite($msg);
                $spiderId = -1;
            }
            $this->saveSpiderHistory($item['url'], $spiderId);
        }
    }

    /**
     * @description 从文件读取抓取配置
     * @return array
     */
    public function getSourceList()
    {
        if (!file_exists($this->sources_path)) {
            Error::triggerError('抓取文件目录不存在');
        }
        $sourcesList = scandir($this->sources_path);
        $sources = array();
        foreach ($sourcesList as $source) {
            if (preg_match('/^,.*\.php/', $source)) {
                $sourceArr = require $this->sources_path . $source;
                if(empty($sourceArr)) {
                    continue;
                }
                $urls = array();
                if(isset($sourceArr['list_urls'])) {
                    $urls = $sourceArr['list_urls'];
                    unset($sourceArr['list_urls']);
                }
                if(isset($sourceArr['list_url'])) {
                    $urls[] = $sourceArr['list_url'];
                }
                //将分页数据进行拆分
                foreach($urls as $url) {
                    if(preg_match("/^(.*?)\[([0-9]+)-([0-9]+)\](.*?)$/", $url, $match)) {
                        for($i=$match[2]; $i <= $match[3]; $i++) {
                            $sourceArr['list_url'] = $match[1]. $i. $match[4];
                            $sources[] = $sourceArr;
                        }
                    } else{
                        $sourceArr['list_url'] = $url;
                        $sources[] = $sourceArr;
                    }
                }
            }
        }

        $this->source_list = $sources;
        return $this->source_list;
    }

    /**
     * 是否进行采集过对应的url
     * @param $url
     * @return bool
     */
    public function hasSpidered($url)
    {
        $urlKey = md5($url);
        $spiderItem = SpideredModel::where('spidered_key', $urlKey)->first();
        if($spiderItem && $spiderItem->content_id > 0) {
           return true;
        }
        return false;
    }

    /**
     * 记录抓取状态信息
     * @param $url
     * @param $spider_id
     */
    public function saveSpiderHistory($url, $spider_id) {
        $spiderKey = md5($url);
        $spideredItem = SpideredModel::where('spidered_key', $spiderKey)->first();
        if(!$spideredItem) {
            //新增
            $data = array(
                'url' => $url,
                'spidered_key' => $spiderKey,
                'content_id'   => $spider_id,
            );
            SpideredModel::create($data);
        } else {
            //update
            if($spider_id < 0) {
                $spideredItem->content_id  -= 1;
            } else {
                $spideredItem->content_id = $spider_id;
            }
            $spideredItem->save();
        }
    }

    private function _clearContentAttribute($html)
    {
        $html = preg_replace("/<script>.*<\/script>/is", '', $html);
        $html = strip_tags($html, '<br><p><img><table><td><tr>');
        $html = preg_replace("/<p[^>]*>/i", '<p>', $html);
        $html = preg_replace("/<\/p>/i", '</p>', $html);
        $html = preg_replace("/<!--.*-->/", '', $html);
        $html = preg_replace("/<p>\s*<\/p>/", '', $html);
        return $html;
    }
    
    private function getItem($source, $link, $type)
    {
        $content = $this->_readFromUrl($link['link'], $type);
        $content = str_replace("\r", '', $content);

        $output = array(
            'url' => $link['link'],
        );
        for ($i = 1; $i < 20; $i++) {
            if (!isset($source['start' . $i]) || !isset($source['end' . $i]) || empty($source['start' . $i]) || empty($source['end' . $i])) {
                break;
            }
            $field_start = $source['start' . $i];
            $field_end = $source['end' . $i];
            $field = $source['field' . $i];

            if (!preg_match("#$field_start(.*?)$field_end#s", $content, $match)) {
                $msg = "其中的  >>{$field}<<  字段抓取失败!";
                Error::logWrite($msg);
            }

            $output[$field] = isset($match[1]) ? $match[1] : '';
        }
        return $output;
    }

    private function _parseLink($list)
    {
        $html = strip_tags($list, '<a><title><link>');
        preg_match_all("/<\s*a.*?href\s*=(.+?)(\s+.*?)?>(.*?)<\s*\/a\s*>/isx", $html, $matchs);
        preg_match_all("/<title>(.+?)<\/title>\s*<link>(.+?)<\/link>/isx", $html, $matchs2);
        $links = array();

        foreach ($matchs[1] as $key => $link) {
            $link = str_replace('\'', '', $link);
            $link = str_replace('"', '', $link);
            $links[$link] = '';
        }
        foreach ($matchs2[2] as $key => $link) {
            $link = str_replace('\'', '', $link);
            $link = str_replace('"', '', $link);
            $title = $matchs2[1][$key];
            $links[$link] = $title;
        }
        return $links;
    }

    private function _formatUrl($baseUrl, $targetUrl)
    {
        if ($targetUrl == '' || $baseUrl == '') {
            return false;
        }
        $urlInfo = parse_url($targetUrl);
        if (isset($urlInfo['scheme']) && ($urlInfo['scheme'] == 'http' || $urlInfo['scheme'] == 'https')) {
            return $targetUrl;
        }
        $urlInfo = parse_url($baseUrl);
        if (substr($targetUrl, 0, 1) == '/') {
            return $urlInfo['scheme'] . '://' . $urlInfo['host'] . $targetUrl;
        }

        if (!isset($urlInfo['path']) || $urlInfo['path'] == '/') {
            return $urlInfo['scheme'] . '://' . $urlInfo['host'] . '/' . $targetUrl;
        }
        $dirName = dirname($urlInfo['path']);
        $dirName = str_replace('\\', '', $dirName);
        return $urlInfo['scheme'] . '://' . $urlInfo['host'] . $dirName . '/' . $targetUrl;
    }

    private function _readFromUrl($url, $ext = array(), $type = '')
    {
        $readUrl = new ReadUrl();
        $htmlContent = $readUrl->readFromUrl($url, $ext, $type);
        return $htmlContent;
    }
}
