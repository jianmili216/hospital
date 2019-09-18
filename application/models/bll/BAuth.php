<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 21:05
 */
namespace App\Model\Bll;

use App\Model\Dao\Auth;


class BAuth extends Base
{

    // 获取用户权限列表
    public static function getUserAuth($auth_ids = '')
    {
        if ($auth_ids) {
            $auth_arr = array_map(function ($v) {
                return intval($v);
            }, explode(',', $auth_ids));
            return Auth::query()->whereIn('id', $auth_arr)->orderBy('sort')->get()->toArray();
        } else {
            return Auth::query()->orderBy('sort')->get()->toArray();
        }
    }

    //所以权限列表
    public static function getData()
    {
        return Auth::query()->get()->toArray();
    }

    //权限
    public static function getInfo($id)
    {
        return Auth::query()->where('id', $id)->first()->toArray();
    }

    //权限更新
    public static function update($id, $data)
    {
        return Auth::query()->where('id', $id)->update($data);
    }

    //检测当前权限是否第三级（当前权限的pid）
    public static function isThree($pid)
    {
        $query = Auth::query()->where('id', $pid)->selectRaw('pid')->first();
        if(!$query)
        {
            return true;
        }else{
            $data = $query->toArray();
            if($data['pid'] != 0)
            {
                return false;
            }else{
                return true;
            }
        }

    }

    //添加权限
    public static function create($data)
    {
        return Auth::query()->firstOrCreate($data);
    }

    //批量删除权限
    public static function delete_all($ids)
    {
        return Auth::query()->whereIn('id', $ids)->delete();
    }
}