<?php
use Spider\Spider;

function hello($s) {
    if(preg_match("/\w+\/\d+\.html$/", $s, $ma)) {
        return $s;
    }
    return false;
}

require __DIR__ . '/../../public/index.php';

$test = new Spider();
$sourceList = $test->getSourceList();

//Spider::getSourceList();
//Spider::getContentList();
//Spider::getItems();


foreach($sourceList as $list) {
    $list_content = $test->getContentList($list);

    $links = $test->getLinkList($list, $list_content);

    $items = $test->getItems($list, $links);
        
}

