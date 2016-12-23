<?php
/**
 * File: SpiderModel.php
 * User: zhoucong@yongche.com
 * Date: 16/12/16
 * Time: 上午11:54
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

Class SpideredModel extends Model {

    protected $primaryKey = 'spidered_id';
    protected $table = 'spidered';
//    public $timestamps = false;

    protected $fillable = array(
        'spidered_key',
        'url',
        'content_id',
    );

}