<?php
/**
 * File: bootstrap.php
 * User: zhoucong@yongche.com
 * Date: 16/12/20
 * Time: 上午9:46
 */
use Illuminate\Database\Capsule\Manager as Capsule;

require  __DIR__ . '/../vendor/autoload.php';

//暂时放在这里
function view($view, $params=array()){
    foreach($params as $key => $value) {
        if(is_int($key) || empty($value)) {
            continue;
        }
        $$key = $value;
    }
    require __DIR__ . '/../resources/views/'. $view. '.tpl';
}

$capsule = new Capsule();
$capsule->addConnection(require  __DIR__ . '/../config/database.php');
$capsule->bootEloquent();

