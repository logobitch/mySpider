<?php
/**
 * File: TextModel.php
 * User: zhoucong@yongche.com
 * Date: 16/12/21
 * Time: 下午6:50
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Text extends Model {
    protected $table = 'texts';
    protected $primaryKey = 'text_id';

    protected $fillable = array(
        'item_id',
        'content',
    );
}