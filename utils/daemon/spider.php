<?php
use Spider\Spider;

function hello($s) {
    if(preg_match("/\w+\/\d+\.html$/", $s, $ma)) {
        return $s;
    }
    return false;
}

require __DIR__ . '/../../src/bootstrap.php';


$spider = new Spider();
$spider->daemon();

