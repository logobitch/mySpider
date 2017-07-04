<?php
set_time_limit(0);
use SP\Spider\Spider;

require __DIR__ . '/../../src/bootstrap.php';

try{
    $spider = new Spider();
    $spider->daemon();
} catch(Exception $e) {
    var_dump($e->getMessage());
}

function segmentfault_url_function($url){
    if(preg_match('/.*\/a\/\d/', $url)) {
        return $url;
    }
}


