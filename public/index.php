<?php
use Illuminate\Database\Capsule\Manager as Capsule;

require  __DIR__ . '/../vendor/autoload.php';

$capsule = new Capsule();
$capsule->addConnection(require  __DIR__ . '/../config/database.php');
$capsule->bootEloquent();


