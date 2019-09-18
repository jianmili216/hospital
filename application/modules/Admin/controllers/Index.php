<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 20:52
 */
use App\Library\Core\AdminController;
use App\Library\Response\ErrorCode;



class IndexController extends AdminController
{

    protected static $validator_rules = [
        'clickdown' => [
            'type|类型' => 'required|integer',
            'uid|用户id' => 'required|integer',
            'referer|来源' => 'required|string',
        ],
    ];

    // 后台仪表盘页
    public function indexAction()
    {

    }

    /**
     * wap/bbs 下载点击上报
     * url: https://aec.tm51.com/admin/index/clickdown
     * params：
     *    type    1:ios 2:android
     *    uid     未登录传0
     *    referer 来源 bbs wap
     */
    public function clickDownAction()
    {
        if ($this->isAjax()) {
            $data['type'] = $this->params['type'];
            $data['uid'] = $this->params['uid'];
            $data['referer'] = $this->params['referer'];
            \App\Model\Bll\BDown::create($data);
            $this->successResponse();
        }
    }
}