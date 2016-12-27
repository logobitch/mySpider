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

    public static function createNewItem($data) {
        $item = self::create($data);
        $itemId = $item->item_id;
        $textData = array(
            'item_id' => $itemId,
            'content' => isset($data['content']) ? trim($data['content']) : '',
        );
        Text::create($textData);
        return $itemId;
    }

    public function getItemList($page = 1) {
        $items = self::all();
        foreach($items as $item) {
            $item['content'] = Text::where('item_id', $item['item_id'])->first()->content;
        }
        return $items->toArray();
    }
}