<?php

return array(
//    'list_url' => 'http://www.430001.com/category/2.html',        //只对单页进行内容的抓取
    'list_url' => 'http://www.430001.com/category/2-[0-9].html',    //有分页,对2-0到2-9之间的页码进行抓取
//    'list_urls' => array(
//        'http://www.430001.com/category/2[0-9].html',     //多维分页,该规则中包含了40个页面,但是参数名称为list_urls
//        'http://www.430001.com/category/3[0-9].html',
//        'http://www.430001.com/category/4[0-9].html',
//        'http://www.430001.com/category/5[0-9].html',
//    ),
    'name'  => 'dengluxinsanban',
    'ext' => '',
    'list_start' => '<div class=\"newsbox newsbox_infor\">',
    'list_end' => '<div class=\"top_hot_box\">',
    'list_separator' => '<div class="news_list">',
    'url_function' => 'hello',

    'start1' => '<h3>',     //这里的1,2标示与后面的field相对应
    'end1' => '<\/h3>',
    'start2' => '<span>',
    'end2' => '<\/span>&nbsp;&nbsp;',
    'start3' => '<div class=\"text_words\">',
    'end3'   => '<div class=\"attach\">',

    'field1' => 'title',
    'field2' => 'source',
    'field3' => 'content',

    'output_sub_title' => 'this is sub title',      //定死的输出规则,可以针对不通的站点进行单独配置
    'output_author' => 'zhoucong',
    'output_starttime' => '20161212',
    'output_endtime' => '20161214',
);
