<?php

namespace App\Utils;


use App\Model\Bll\BAuth;
use App\Model\Bll\BRole;

class Auth
{

    const SUPER_ADMIN = 1;

    // 检查用户权限
    public static function check($admin, $current_request)
    {
        if (empty($_SESSION['auth_access_list'])) {
            $all_auth = self::getAccessList();
            $_SESSION['auth_access_list'] = array_unique(array_column($all_auth, 'action'));
        }
        $auth = self::getAccessList($_SESSION['admin_info']);
        if (empty($_SESSION['admin_access_list'])) {
            $_SESSION['admin_access_list'] = array_unique(array_column($auth, 'action'));
        }
        if (empty($_SESSION['admin_nav'])) {
            $_SESSION['admin_nav'] = self::getNav($auth);
        }
        if ($admin['id'] == self::SUPER_ADMIN) return true;
        if (!in_array($current_request, $_SESSION['auth_access_list'])) return true;
        return in_array($current_request, $_SESSION['admin_access_list']);
    }

    // 获取用户权限字典
    public static function getAccessList($admin = [])
    {
        if ($admin) {
            $role = BRole::getUserRole($admin['role_id']);
            return BAuth::getUserAuth($role['auth_ids']);
        } else {
            return BAuth::getUserAuth();
        }
    }

    // 获取菜单栏
    public static function getNav($arr, $pid = 0)
    {
        $tree = [];
        foreach ($arr as $v) {
            if (!isset($v['pid'])) {
                $tree[] = $v;
            } elseif ($v['pid'] == $pid) {
                $v['children'] = self::getNav($arr, $v['id']);
                if (empty($v['children'])) unset($v['children']);
                $tree[] = $v;
            }
        }
        return $tree;
    }
}
