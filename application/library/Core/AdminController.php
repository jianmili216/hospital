<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/10
 * Time: 15:00
 */

namespace App\Library\Core;

use App\Model\Bll\BMessage;
use App\Utils\Auth;
use App\Utils\ConfigLoader;

// admin 基类
class AdminController extends Controller
{

    const ALLOW_ACTION = ['login', 'logout', 'clickdown'];
    const start_time   = ' 00:00:00';
    const end_time     = ' 23:59:59';

    public function init()
    {
        parent::init();
        $this->checkLogin();
        $this->refreshExpire();
        if ($this->isAjax()) {
            $this->initValidator();
        }
        if (!empty($_SESSION['admin_info'])) {
            $current_request = '/' . strtolower(implode('/', [
                    $this->request->getModuleName(),
                    $this->request->getControllerName(),
                    $this->request->getActionName()
                ]));
            $check = Auth::check($_SESSION['admin_info'], $current_request);
            if (!$check) die('您没有权限访问该版块！');
//            BMessage::getAecMessagesTotal();
        }
    }

    private function checkLogin()
    {
        $action = $this->request->action;
        $check = in_array($action, self::ALLOW_ACTION);
        if (!$check && !isset($_SESSION['admin_info'])) {
            $this->redirect('/admin/login/login');die;
        }

        return true;
    }

    private function refreshExpire()
    {
        $session_config = ConfigLoader::get("application.session");
        if ($this->cookie($session_config['name']) && !empty($_SESSION['admin_info'])) {
            setcookie($session_config['name'], session_id(), time() + $session_config['expire'], '/');
        }
    }
}