<?php
/**
 * FILE: segment.php
 * DESCRIPTION: segmentfault web
 * User: zhoucong
 * Date: 2017/6/29
 */
return array(
    'list_url'  => 'https://segmentfault.com/blogs/hottest',
    'name'      => 'segmentfault',
    'list_start'    => '<div class="stream-list blog-stream">',
    'list_end'      => '<div class="text-center">',
    'ext'   => '',

    'list_separator'    => '<\/section>',
    'url_function'      => 'segmentfault_url_function',

//    'debug_list_content' => '1',
//    'debug_mode'    => '1',

    'start1'    => '<a href="\/a\/\d+">',
    'end1'      => '<\/a>',
    'field1'    => 'title',

    'start2'    => '<a href="\/u\/\w+" class="mr5\s+"><strong>',
    'end2'      => '<\/strong>',
    'field2'    => 'author',

    'start3'    => '<div class="article fmt article__content" data-id="\d+" data-license="\w+">',
    'end3'      => '<div class="clearfix mt10">',
    'field3'    => 'content',
);