<?php
/**
 * File: SpideredModel.php
 * User: zhoucong@yongche.com
 * Date: 16/12/21
 * Time: 上午10:25
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