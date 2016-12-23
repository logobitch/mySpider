<?php
/**
 * File: ItemModel.php
 * User: zhoucong@yongche.com
 * Date: 16/12/21
 * Time: 下午6:46
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model {
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
    
    public static function createNewItem($data) {
        $item = self::create($data);
        $itemId = $item->item_id;
        $textData = array(
            'item_id' => $itemId,
            'content' => isset($data['content']) ? $data['content'] : '',
        );
        TextModel::create($textData);
        return $itemId;
    }
}