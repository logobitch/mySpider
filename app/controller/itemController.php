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
        $lists = \App\Model\Item::getItemList();

        return view('item/index', array('lists'=>$lists));
    }
}