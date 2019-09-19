<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 22:21
 */
use App\Library\Core\AdminController;
use App\Library\Response\ErrorCode;
use App\Model\Bll\BAdmin;
use App\Model\Bll\BRole;
use App\Model\Bll\BUser;
use App\Model\Bll\BAuth;
use App\Utils\Page;
use App\Model\Dao\Role;
use App\Model\Dao\User;
use App\Utils\Auth;


class AuthController extends AdminController
{

    protected static $validator_rules = [
        'adminadd' => [
            'role_id|角色' => 'required|integer',
            'name|用户名' => 'required',
            'password|密码' => 'required',
        ],
        'authedit' => [
            'id|权限id' => 'required|integer',
            'pid|父级权限id' => 'required|integer',
            'title|权限名' => 'required',
            'action|地址' => 'required',
            'sort|排序' => 'required',
        ],
        'authadd' => [
            'pid|父级权限id' => 'required|integer',
            'title|权限名' => 'required',
            'action|地址' => 'required',
            'sort|排序' => 'required',
        ],
        'roleauthedit' => [
            'id|角色id' => 'required|integer',
        ],
        'roleadd' => [
            'name|角色名称' => 'required',
            'describe|描述' => 'required',
            'ids|权限列表' => 'required',
        ],
    ];


    // 管理员列表
    public function adminAction()
    {
        $this->getView()->assign('role', BRole::getRoleList());
        $this->getView()->assign('data', BAdmin::getUserList());
    }


    // 新增管理员
    public function adminAddAction()
    {
        if ($this->post()) {
            $errors = parent::initValidator();
            if (empty($errors)) {
                $data['role_id'] = $this->params['role_id'];
                $data['name'] = $this->params['name'];
                $data['mobile'] = $this->params['mobile'];
                $data['email'] = $this->params['email'];
                $data['salt'] = rand(100000, 999999);
                $data['password'] = md5(md5($this->params['password']) . $data['salt']);
                $admin = BAdmin::getUserOne(['name' => $this->params['name']]);
                if ($admin){
                    $errors['error'] = [ErrorCode::$error_code_mapping[ErrorCode::USER_REPEAT]];
                    $this->getView()->assign('errors', $errors);
                }else{
                    BAdmin::create($data);
                    $this->redirect('/admin/auth/admin');
                }
            } else {
                $this->getView()->assign('errors', $errors);
            }
        }
        $this->getView()->assign('role', BRole::getRoleList());
    }


    // 更新管理员
    public function adminEditAction()
    {
        $admin = BAdmin::getUserOne(['id' => $this->params['id']]);
        if ($this->post()) {
            $errors = parent::initValidator();
            if (empty($errors)) {
                if (!empty($this->params['name'])) {
                    $name = trim($this->params['name']);
                    $data['name'] = $name;
                }
                if (!empty($this->params['password'])) {
                    $data['password'] = md5(md5($this->params['password']) . $admin['salt']);
                }
                $data['role_id'] = $this->params['role_id'];
                $data['mobile'] = $this->params['mobile'];
                $data['email'] = $this->params['email'];
                $data['salt'] = rand(100000, 999999);
                $data['password'] = md5(md5($this->params['password']) . $data['salt']);
                $admin = BAdmin::getUserOne(['name' => $this->params['name']]);
                if ($admin){
                    $errors['error'] = [ErrorCode::$error_code_mapping[ErrorCode::USER_REPEAT]];
                    $this->getView()->assign('errors', $errors);
                }else{
                    BAdmin::update($this->params['id'], $data);
                    $this->redirect('/admin/auth/admin');
                }
            } else {
                $this->getView()->assign('errors', $errors);
            }
        }
        $this->getView()->assign('data', $admin);
        $this->getView()->assign('role', BRole::getRoleList());
    }


    // 删除
    public function adminDelAction()
    {
        $data = BAdmin::delete($this->params['id']);
        $this->successResponse($data);
    }


    // 角色列表
    public function roleAction()
    {
        $is_admin = 0;
        if ($_SESSION['admin_info']['id'] != 1) {
            $data = BRole::getRoleList();
        } else {
            $is_admin = 1;
            $data = BRole::getAllRoleList();
        }

        $this->getView()->assign('is_admin', $is_admin);
        $this->getView()->assign('data', $data);
    }


    public function showNameAction()
    {
        if ($this->post()) {
            BAdmin::update($this->params['admin_id'], ['is_show_name' => $this->params['is_show_name']]);
        }
        $this->successResponse();
    }


    public function setEssenceAction()
    {
        if ($this->post()) {
            BAdmin::update($this->params['admin_id'], ['set_essence' => $this->params['set_essence']]);
        }
        $this->successResponse();
    }

    //权限列表
    public function detailAction()
    {
        $data = BAuth::getData();
        $data = sortAuth($data);
        foreach ($data as $key => &$value) {
            # code...
            $value['level'] = str_repeat('----', $value['level']);
        }
        $this->getView()->assign('data', $data);
    }

    //权限编辑
    public function authEditAction()
    {
        if ($this->post()) {
            $errors = parent::initValidator();
            if (empty($errors)) {
                if ($this->params['id'] == $this->params['pid']) {
                    $this->getView()->assign('errors', [['自己不能成为自己的上级']]);
                } else {
                    //排除第三级权限
                    $check = BAuth::isThree($this->params['pid']);

                    if ($check) {
                        $params['title'] = $this->params['title'];
                        $params['action'] = $this->params['action'];
                        $params['icon'] = $this->params['icon'];
                        $params['sort'] = $this->params['sort'];
                        $params['pid'] = $this->params['pid'];

                        $res = BAuth::update($this->params['id'], $params);

                        if ($res) {
                            $this->updateUserAccess();
                            $this->redirect('/admin/auth/detail');
                        } else {
                            $this->getView()->assign('errors', [[ErrorCode::$error_code_mapping[ErrorCode::FAILURE]]]);
                        }
                    } else {
                        $this->getView()->assign('errors', [['暂时不支持添加第三级权限']]);
                    }
                }

            } else {
                $this->getView()->assign('errors', $errors);
            }
        }
        $data = BAuth::getInfo($this->params['id']);
        $allAuth = BAuth::getData();
        $allAuth = sortAuth($allAuth);

        foreach ($allAuth as $key => &$value) {
            # code...
            $value['level'] = str_repeat('----', $value['level']);
        }
        //插入顶级字段
        array_unshift($allAuth, ['id' => 0, 'title' => '顶级权限', 'level' => '']);
        $this->getView()->assign('data', $data);
        $this->getView()->assign('all_auth', $allAuth);
    }

    //权限添加
    public function authAddAction()
    {
        if ($this->post()) {
            $errors = parent::initValidator();
            if (empty($errors)) {
                //排除第三极权限
                $check = BAuth::isThree($this->params['pid']);
                if ($check) {
                    $params['title'] = $this->params['title'];
                    $params['action'] = $this->params['action'];
                    $params['icon'] = $this->params['icon'];
                    $params['sort'] = $this->params['sort'];
                    $params['pid'] = $this->params['pid'];

                    $res = BAuth::create($params);
                    if ($res) {
                        $this->redirect('/admin/auth/detail');
                    } else {
                        $this->getView()->assign('errors', [[ErrorCode::$error_code_mapping[ErrorCode::FAILURE]]]);
                    }

                } else {

                    $this->getView()->assign('errors', [['暂时不支持添加第三级权限']]);
                }
            } else {
                $this->getView()->assign('errors', $errors);
            }
        }
        $allAuth = BAuth::getData();
        $allAuth = sortAuth($allAuth);
        $pid = isset($this->params['id']) ? $this->params['id'] : 0;
        foreach ($allAuth as $key => &$value) {
            # code...
            $value['level'] = str_repeat('----', $value['level']);
        }
        //插入顶级字段
        array_unshift($allAuth, ['id' => 0, 'title' => '顶级权限', 'level' => '']);

        $this->getView()->assign('pid', $pid);
        $this->getView()->assign('all_auth', $allAuth);
    }

    //删除权限
    public function authDelAction()
    {
        $ids = array_map(function ($v) {
            return $v;
        }, explode(',', $this->params['id']));

        $data = BAuth::delete_all($ids);
        if ($data) {
            $this->updateUserAccess();
            $this->successResponse();
        } else {
            $this->errorResponse();
        }
    }

    //角色对应的权限列表
    public function roleAuthEditAction()
    {
        if ($this->post()) {
            $errors = parent::initValidator();
            if (empty($errors)) {

                $update['auth_ids'] = $this->params['ids'];
                $res = BRole::update($this->params['id'], $update);

                //刷新菜单权限
                $this->updateUserAccess();

                if ($res) {
                    $this->successResponse();
                } else {
                    $this->errorResponse(ErrorCode::FAILURE);
                }
            } else {
                $this->getView()->assign('errors', $errors);
            }
        }
        //个人角色权限
        $role = BRole::getUserRole($this->params['id']);

        //所有权限
        $allAuth = BAuth::getData();
        $allAuth = sortAuth($allAuth);

        foreach ($allAuth as $key => &$value) {
            # code...
            $value['level'] = str_repeat('----', $value['level']);
        }

        //个人auth_ids拆成数组
        $role_auth = explode(',', $role['auth_ids']);

        $this->getView()->assign('role_auth', $role_auth);
        $this->getView()->assign('allAuth', $allAuth);
        $this->getView()->assign('data', $role);
    }

    //角色添加
    public function roleAddAction()
    {
        if ($this->post()) {
            $errors = parent::initValidator();
            if (empty($errors)) {

                $data['auth_ids'] = $this->params['ids'];
                $data['name'] = $this->params['name'];
                $data['describe'] = $this->params['describe'];

                $res = BRole::create($data);
                if ($res) {
                    $this->successResponse();
                } else {
                    $this->errorResponse(ErrorCode::FAILURE);
                }
            } else {
                $this->getView()->assign('errors', $errors);
            }
        }

        //所有权限
        $allAuth = BAuth::getData();
        $allAuth = sortAuth($allAuth);

        foreach ($allAuth as $key => &$value) {
            # code...
            $value['level'] = str_repeat('----', $value['level']);
        }
        $this->getView()->assign('allAuth', $allAuth);
    }

    //角色删除
    public function roleDelAction()
    {
        $ids = array_map(function ($v) {
            return $v;
        }, explode(',', $this->params['id']));

        $data = BRole::delete_all($ids);
        if ($data) {
            $this->successResponse();
        } else {
            $this->errorResponse();
        }
    }

    //管理员行为
    public function deedAdminAction()
    {
        $query = \App\Model\Dao\AdminAction::query()
            ->from('admin_action as aa')
            ->select('aa.*', 'a.name')
            ->join('admin as a', 'aa.aid', '=', 'a.id');
        $search = [];
        //操作内容
        if (isset($this->params['action']) && $this->params['action'] != -2) {
            $query->where('aa.action', $this->params['action']);
            $search['action'] = $this->params['action'];
        }
        //操作类型
        if (isset($this->params['type']) && $this->params['type'] != -2) {
            $query->where('aa.type', $this->params['type']);
            $search['type'] = $this->params['type'];
        }
        if (!empty($this->params['keyword'])) {
            $keyword = $this->params['keyword'];
            $query->where(function ($query) use ($keyword) {
                if (is_numeric($keyword)) {
                    $query->where("aa.aid", $keyword)
                        ->orWhere("aa.target_id", "like", "%{$keyword}%");
                } else {
                    $query->where("a.name", "like", "%{$keyword}%");
                }
            });
            $search['keyword'] = $this->params['keyword'];
        }
        $page_total = $query->count();
        $data = $query->orderBy('aa.id', 'desc')
            ->offset($this->page_size * ($this->page - 1))
            ->limit($this->page_size)
            ->get()->toArray();
        $url = !empty($search) ? '?' . http_build_query($search) . "&page={page}" : "?page={page}";
        $paginator = new Page($page_total, $this->page_size, $this->page, urldecode($url));
        $this->getView()->assign('data', $data);
        $this->getView()->assign('search', $search);
        $this->getView()->assign('page_html', $paginator->show());
    }

    //更新用户权限
    public
    function updateUserAccess()
    {
        $auth = Auth::getAccessList($_SESSION['admin_info']);
        $_SESSION['admin_access_list'] = array_unique(array_column($auth, 'action'));

        $_SESSION['admin_nav'] = Auth::getNav($auth);
    }



}