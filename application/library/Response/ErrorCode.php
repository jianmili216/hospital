<?php
/**
 * Created by PhpStorm.
 * User: weijin
 * Date: 17/8/31
 * Time: 15:34
 */

namespace App\Library\Response;

// 公共错误码
class ErrorCode
{

    // 系统级别
    const SUCCESS = 0;
    const NOT_FOUND = 10001;
    const FAILURE = 10002; // 应用自定义错误
    const SYSTEM_ERROR = 10003; // 系统错误使用
    const DB_ERROR = 10004; // 数据库错误
    const NO_DATA = 10005;
    // 请求级别
    const MUST_GET = 10101;
    const MUST_POST = 10102;
    const MUST_HTTPS = 10103;
    const SIGN_MISS_ERROR = 10104;
    const SIGN_VALIDATOR_ERROR = 10105;
    const REQUEST_TIME_OUT = 10106;
    const PARAM_VALIDATOR_ERROR = 10107;
    const PARAM_IS_INVALID = 10108;
    // 上传
    const NO_FILES = 10201;
    const UPLOAD_FILE_ERROR = 10202;
    const UPLOAD_FILE_FAILURE = 10203;
    const UPLOAD_FILE_PART_SUCCESS = 10204;
    const UPLOAD_FILE_NOT_FIELD = 10205;
    const UPLOAD_FILE_SIZE_ASTRICT = 10206;
    // 用户登录
    const NO_LOGIN = 10301;
    const LOGIN_BASE_ERROR = 10302;
    const LOGIN_VCODE_ERROR = 10303;
    const NO_USER = 10304;
    const USER_REPEAT = 10305;
    const USER_OLD_PWD_ERROR = 10306;
    const USER_OLD_MOBILE_ERROR = 10307;
    const USER_EMAIL_ERROR = 10308;
    const USER_MOBILE_BIND = 10309;
    const MOBILE_BIND_ERROR= 10310;
    const USER_USERNAME_LEN_ERROR = 10311;
    // 微信登录
    const WX_ACCESS_TOKEN_FAILURE = 10401;
    const WX_USER_INFO_FAILURE = 10402;
    // 邮箱认证
    const MAIL_SEND_FAILURE = 10501;
    const MAIL_VALIDATOR_ERROR = 10502;
    const MAIL_CODE_EXPIRE = 10503;
    const MAIL_RULE_ERROR = 10504;

    #手机号验证码
    const VCODE_REMIND = 10600;

    #帖子
    const POST_DELETE = 131400;
    const POST_NOT_FOUND = 131401;

    #邀请
    const INVITATION_CODE_ERROR = 10900;
    const INVITATION_CODE_FALSE = 10910;
    const INVITATION_CODE_INVALID = 10920;
    const INVITATION_ALREADY = 10930;
    const INVITATION_FAIL = 10940;

    public static $error_code_mapping = [

        self::SUCCESS => 'success',
        self::NOT_FOUND => 'not found',
        self::FAILURE => 'failure',
        self::SYSTEM_ERROR => '系统错误',
        self::DB_ERROR => '数据库错误',
        self::NO_DATA => '未查询到符合条件的数据',

        self::MUST_GET => '需要GET请求',
        self::MUST_POST => '需要POST请求',
        self::MUST_HTTPS => '需要HTTPS请求',
        self::SIGN_MISS_ERROR => '缺少签名',
        self::SIGN_VALIDATOR_ERROR => '签名错误',
        self::REQUEST_TIME_OUT => '请求超时',
        self::PARAM_VALIDATOR_ERROR => '参数验证错误',
        self::PARAM_IS_INVALID => '参数无效',

        self::NO_LOGIN => '登陆失效，请重新登陆',
        self::LOGIN_BASE_ERROR => '用户名或密码错误',
        self::LOGIN_VCODE_ERROR => '验证码错误',
        self::NO_USER => '用户不存在',
        self::USER_REPEAT => '用户名重复',
        self::USER_OLD_PWD_ERROR => '旧密码匹配错误',
        self::USER_OLD_MOBILE_ERROR => '旧手机号匹配错误',
        self::USER_EMAIL_ERROR => '邮箱匹配错误',
        self::USER_MOBILE_BIND => '该手机号已绑定其他账号',
        self::MOBILE_BIND_ERROR => '请输入已绑定的手机号！',
        self::USER_USERNAME_LEN_ERROR => '用户名长度不合法',

        self::NO_FILES => '没有任何文件被上传',
        self::UPLOAD_FILE_ERROR => '文件有误',
        self::UPLOAD_FILE_FAILURE => '上传文件失败',
        self::UPLOAD_FILE_PART_SUCCESS => '导入的部分数据出现错误，请下载错误文件',
        self::UPLOAD_FILE_NOT_FIELD => '上传参数file不存在',
        self::UPLOAD_FILE_SIZE_ASTRICT => '上传文件最大为5M',

        self::WX_ACCESS_TOKEN_FAILURE => 'access_token获取失败',
        self::WX_USER_INFO_FAILURE => '用户信息获取失败',

        self::MAIL_SEND_FAILURE => '邮件发送失败',
        self::MAIL_VALIDATOR_ERROR => '邮件验证码错误',
        self::MAIL_CODE_EXPIRE => '邮件验证码已过期',
        self::MAIL_RULE_ERROR => '邮箱格式验证错误',

        self::VCODE_REMIND  => '您的短信验证码还未过期,请两分钟后再试！',

        self::POST_DELETE   => '该贴已被删除!',
        self::POST_NOT_FOUND   => '该贴不存在!',

        self::INVITATION_CODE_ERROR => '邀请码不存在',

        self::INVITATION_CODE_FALSE => '不能邀请自身',
        self::INVITATION_CODE_INVALID => '不能相互邀请',
        self::INVITATION_ALREADY => '您已经被邀请过！',
        self::INVITATION_FAIL =>'很遗憾！您不能被新用户邀请～',

    ];

}