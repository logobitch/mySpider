#文章采集
>使用php编写,结合自定义的正则表达式,对于较为繁琐的数据库的操作,引入了laravel中的`illuminate`完成。目前依赖自己编写简单的正则表达式完成内容抓取。

##抓取的大致步骤
- 在`config/spiders`文件夹下创建抓取的规则文件,凡是文件名称以`,`开头的,表示按照该文件中的规则进行抓取,其余的文件忽略
    - 抓取的规则文件中的形式:
    ```
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
    ```
- 根据规则文件中的`list_url`或`list_urls`得到抓取的规则数组,如果涉及到多个分页设置的话,会自动生成多个抓取的规则数组,只是在列表页url上体现出差异来

- 之后根据规则数组读取列表页中的列表部分
- 根据规则文件对列表进行切割,得到每个切割部分中的标题和详情页链接地址
- 根据得到的链接地址和规则信息,抓取界面中的相关内容,然后得到结果数组
- 利用`illuminate`提供的方法存库