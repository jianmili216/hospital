<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 17:10
 */
namespace App\Plugin;

use App\Utils\ConfigLoader;


class Hook extends \Yaf\Plugin_Abstract
{
    protected static $default_module = 'Admin';
    protected static $allow_action = ['login', 'getpost', 'getmessage', 'createtag', 'wthread', 'wdiary'];
    protected static $admin_login_action = ['/','/admin','/admin/','/admin/login','/admin/login/','/admin/login/login','/admin/login/login/'];

    public function routerStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        if(ConfigLoader::get('url.suffix')) {
            if(strtolower(substr($_SERVER['REQUEST_URI'], - strlen(ConfigLoader::get('url.suffix')))) == strtolower(ConfigLoader::get('url.suffix'))) {
                $request->setRequestUri(substr($_SERVER['REQUEST_URI'], 0 , - strlen(ConfigLoader::get('url.suffix'))));
            }
        }
        if (in_array(strtolower($_SERVER['REQUEST_URI']), static::$admin_login_action)) {
            $request->setRequestUri('/admin/login/login');
        }
    }

    public function routerShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response){}

    public function dispatchLoopStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response){}

    public function ppatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response){}

    public function postDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        if ($request->getModuleName() == static::$default_module) {
            if (in_array(strtolower($request->getActionName()), static::$allow_action)) {
                return true;
            }
            $body = $response->getBody();
            $layout = new \Yaf\View\Simple(BASE_PATH .'/application/modules/Admin/views');
            $layout->assign('content', $body);
            $response->setBody($layout->render('Layout.phtml'));
        }
    }

    public function dispatchLoopShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response){}
}
