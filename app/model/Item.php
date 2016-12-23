<?php
/**
 * File: ItemModel.php
 * User: zhoucong@yongche.com
 * Date: 16/12/21
 * Time: 下午6:46
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {
    protected $table = 'items';
    protected $primaryKey = 'item_id';

    protected $fillable = array(
        'title',
        'source',
        'desc',
        'author',
        'order_by',
        'classify_id'
    );

    public function content() {
        return $this->hasOne('App\Model\Text');
    }

    public function createNewItem($data) {
        $item = new self($data);
        if($this->content()->save($item)) {
            return $item->item_id;
        }
        return false;
    }
    
//    public static function createNewItem($data) {
//        $item = self::create($data);
//        $itemId = $item->item_id;
//        $textData = array(
//            'item_id' => $itemId,
//            'content' => isset($data['content']) ? $data['content'] : '',
//        );
//        TextModel::create($textData);
//        return $itemId;
//    }

    public static function getItemList($page = 1) {
    }
}