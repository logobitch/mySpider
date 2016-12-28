<?php
set_time_limit(0);
use SP\Spider\Spider;

function hello($s) {
    if(strpos($s, 'news')) {
        return $s;
    }
    return false;
}

require __DIR__ . '/../../src/bootstrap.php';

try{
    $spider = new Spider();
    $spider->daemon();
} catch(Exception $e) {
    var_dump($e->getMessage());
}


