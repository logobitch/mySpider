<?php

return array(
    'list_url' => 'http://www.430001.com/category/2-[0-9].html',
//    'list_urls' => array(
//        'http://www.430001.com/category/2[0-9].html',
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

    'start1' => '<h3>',
    'end1' => '<\/h3>',
    'start2' => '<span>',
    'end2' => '<\/span>&nbsp;&nbsp;',
    'start3' => '<div class=\"text_words\">',
    'end3'   => '<div class=\"attach\">',

    'field1' => 'title',
    'field2' => 'source',
    'field3' => 'content',

    'output_sub_title' => 'this is sub title',
    'output_author' => 'zhoucong',
    'output_starttime' => '20161212',
    'output_endtime' => '20161214',
);
