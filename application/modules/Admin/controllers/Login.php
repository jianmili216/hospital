<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 11:32
 */


use App\Library\Core\AdminController;
use App\Model\Bll\BAdmin;


class LoginController extends AdminController
{

    protected static $validator_rules = [
        'login' => [
            'username|管理员名称' => 'required',
            'password|登陆密码' => 'required',
        ],
    ];

    public function loginAction()
    {
        if ($this->isAjax()) {

            $where['name'] = $this->params['username'];
            $admin = BAdmin::getUserOne($where);
//            d($admin);
            if (!$admin) $this->errorResponse('用户名或密码错误');
            $password = md5(md5($this->params['password']) . $admin['salt']);
            if ($password != $admin['password']) $this->errorResponse('用户名或密码错误');
            $admin['head_img'] = $admin['head_img'] ? STATIC_IMAGE_PATH . $admin['head_img'] : '';
            $_SESSION['admin_info'] = $admin;
            if (isMobile()) {
                $redirect = '/admin/thread/wThread';
            } else {
                $redirect = '/admin/index/index';
            }
            $this->successResponse([], '', true, $redirect);
        }
    }

    public function logoutAction()
    {
        unset($_SESSION['admin_info']);
        unset($_SESSION['admin_nav']);
        unset($_SESSION['admin_access_list']);
        $this->redirect('/');
    }
}