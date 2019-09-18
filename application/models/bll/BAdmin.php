<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 16:54
 */
namespace App\Model\Bll;

use App\Model\Dao\Admin;


class BAdmin extends Base
{

    public static function getUserOne($where)
    {
        $admin = Admin::query()->where($where)->first();
        return $admin ? $admin->toArray() : $admin;
    }

    public static function getUserList()
    {
        return Admin::query()->where('status', Admin::STATUS_ACTIVE)->get()->toArray();
    }
    //运营人员列表
    public static function getAdminUser(){
        return Admin::query()->where('role_id', Admin::Admin_Business)->where('status', Admin::STATUS_ACTIVE)->get()->toArray();
    }
    public static function delete($ids)
    {
        $id_arr = array_map(function ($v) {
            return intval($v);
        }, explode(',', $ids));
        return Admin::query()->whereIn('id', $id_arr)->update(['status' => Admin::STATUS_INACTIVE]);
    }

    public static function create($data)
    {
        return Admin::query()->firstOrCreate($data);
    }

    public static function update($id, $data)
    {
        return Admin::query()->where('id', $id)->update($data);
    }

}