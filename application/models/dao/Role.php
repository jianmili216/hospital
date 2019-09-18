<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 21:11
 */
namespace App\Model\Dao;


class Role extends Base
{
    #定义表名称
    protected $table = 'role';
    protected $primaryKey = 'id';

    const SUPER_ROLE = 1;

    public static $role_mapping = [
        self::SUPER_ROLE => "超级管理员",
    ];
}