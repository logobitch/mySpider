<?php
/**
 * File: SpiderModel.php
 * User: zhoucong@yongche.com
 * Date: 16/12/16
 * Time: 上午11:54
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

Class SpiderModel extends Model {
    protected $table = 'spider';
//    public $timestamps = false;

    protected $fillable = array(
        'title',
        'desc',
        'author',
        'editor',
    );

}