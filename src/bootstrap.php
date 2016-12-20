<?php
/**
 * File: bootstrap.php
 * User: zhoucong@yongche.com
 * Date: 16/12/20
 * Time: 上午9:46
 */
use Illuminate\Database\Capsule\Manager as Capsule;

require  __DIR__ . '/../vendor/autoload.php';

$capsule = new Capsule();
$capsule->addConnection(require  __DIR__ . '/../config/database.php');
$capsule->bootEloquent();