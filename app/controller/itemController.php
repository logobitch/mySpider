<?php
/**
 * File: itemController.php
 * User: zhoucong@yongche.com
 * Date: 16/12/19
 * Time: 下午7:16
 */
namespace App\Controller;

class ItemController {
    public function indexAction(){
        $lists = \App\Model\SpiderModel::all()->toArray();
        require __DIR__.'/test.html';
    }
}