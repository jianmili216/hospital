<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 21:10
 */
namespace App\Model\Bll;

use App\Model\Dao\Role;


class BRole extends Base
{

    // 获取用户角色
    public static function getUserRole($id)
    {
        $row = Role::query()->find($id);
        return $row ? $row->toArray() : null;
    }

    // 获取角色列表
    public static function getRoleList()
    {
        return Role::query()->where('id', '!=', Role::SUPER_ROLE)->get()->toArray();
    }

    //更新角色
    public static function update($id, $data)
    {
        return Role::query()->where('id', $id)->update($data);
    }

    //删除角色
    public static function delete_all($ids)
    {
        return Role::query()->whereIn('id', $ids)->delete();
    }

    //新建角色
    public static function create($params)
    {
        return Role::query()->firstOrCreate($params);
    }

    //根据用户id获取列表，
    public static function getAllRoleList()
    {
        $query = Role::query()->get();
        return $query ? $query->toArray() : [];
    }

}