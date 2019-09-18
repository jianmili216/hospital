<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 16:55
 */
namespace App\Model\Dao;


class Admin extends Base
{
    #定义表名称
    protected $table = 'admin';
    protected $primaryKey = 'id';


    # 定义状态
    const STATUS_ACTIVE = 0;
    const STATUS_INACTIVE = -1;
    # 定义状态字典
    public static $status_mapping = array(
        self::STATUS_ACTIVE => "有效",
        self::STATUS_INACTIVE => "无效",
    );

}