<?php
use Spider\Spider;


require __DIR__ . '/../../public/index.php';

$test = new Spider();
$sourceList = $test->getSourceList();

//Spider::getSourceList();
//Spider::getContentList();
//Spider::getItems();


foreach($sourceList as $list) {
    $list_content = $test->getContentList($list);

    $items = $test->getContentItems($list_content, $list);

}

