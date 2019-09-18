<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 16:57
 */
namespace App\Model\Dao;

use \Illuminate\Database\Eloquent\Model;


class Base extends Model
{
    protected $connection = 'default';
    protected $guarded = [];
    public $timestamps = false;


}