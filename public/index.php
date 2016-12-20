<?php
require __DIR__.'/../src/bootstrap.php';

$itemController = new App\Controller\ItemController();
$itemController->indexAction();