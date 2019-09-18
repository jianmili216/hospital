<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/11
 * Time: 18:12
 */

namespace App\Library\Core;

use Yaf;
use App\Library\Response\ErrorCode;
use App\Library\Common\ParamsValidator;
use App\Utils\Logger;

// 公共类
class Controller extends Yaf\Controller_Abstract
{
    protected $request = []; //request对象
    protected $params = []; //请求参数
    protected $server = []; //$_SERVER
    protected $files = []; //上传文件数据
    protected $cookie = []; //cookie数据
    protected $session = []; //session数据
    protected $user_info = []; //用户信息
    protected $remote_ip;  //真实来源IP

    protected $page = 1;
    protected $page_size = 20;
    protected $total = 0;
    protected $filter_html = true; //是否过滤文本标签、默认过滤标签

    protected $validator;
    protected static $validator_rules = [];
    protected static $validate_custom_message = [];
    protected static $logger;


    public function init()
    {
        $this->request = $this->getRequest();
//        $this->session = Yaf\Session::getInstance();
        if ($this->request->isCli()) {
            $this->errorResponse(ErrorCode::FAILURE);
        }
        $this->files = $this->files();
        $this->cookie = $this->cookie();
        $this->server = $this->server();
        $this->remote_ip = getRemoteIP();
        $this->initQueryParams();
    }

    /**
     * 初始化 validation
     * 1、初始化transtor
     * 2、解析控制器的过滤规则
     * 3、解析过滤器的过滤失败信息
     */
    protected function initValidator()
    {
        $rule_key = $this->request->getActionName();
        $validate_rule = isset(static::$validator_rules[$rule_key]) ? static::$validator_rules[$rule_key] : [];
        $validate_custom_message = isset(static::$validate_custom_message[$rule_key]) ? static::$validate_custom_message[$rule_key] : [];
        $this->validator = new ParamsValidator($validate_custom_message);
        $validate_result = $this->validator->runValid($validate_rule, $this->params);
        if ($this->isPost()) {
            return $validate_result;
        } else {
            $this->errorResponse(ErrorCode::PARAM_VALIDATOR_ERROR, $validate_result);
        }
    }

    /**
     * 依照传入的规则和数据进行检查
     * @param NULL
     * @return mixed true|array
     * true 成功过滤,调用$this->_request获得输入数据数组
     * array 过滤失败,过滤失败的信息返回
     */
    protected function validation()
    {
        $result = true;
        $this->validator->invalid();
        $invalid = $this->validator->messages()->toArray();
        if (is_array($invalid) && !empty($invalid)) {
            $result = $invalid;
        }
        return $result;
    }

    protected function disableView()
    {
        $dispatcher = Yaf\Dispatcher::getInstance();
        $dispatcher->autoRender(false);
        $dispatcher->disableView();
        $dispatcher->returnResponse(true);
    }

    protected function response(array $response)
    {
        $this->disableView();
        $response['request_date'] = date('Y-m-d H:i:s');
        if (isset($this->params['callback'])) {
            $method = $this->params['callback'];
            $response_str = $method . '(' . json_encode($response) . ')';
            exit($response_str);
        }
        header('Content-Type: application/json');
        $json_response = json_encode($response, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
        echo str_replace('\\\\', '\\', $json_response);
        exit;
    }

    protected function successResponse($data = [], $message = '', $reload = false, $redirect = '')
    {
        $response['code'] = ErrorCode::SUCCESS;
        $response['message'] = $message ?: ErrorCode::$error_code_mapping[ErrorCode::SUCCESS];
        $response['data'] = $data;
        $response['reload'] = $reload;
        $response['redirect'] = $redirect;
        $this->response($response);
    }

    protected function successPageResponse($items, $message = '', $reload = false, $redirect = '')
    {
        $response['code'] = ErrorCode::SUCCESS;
        $response['message'] = $message ?: ErrorCode::$error_code_mapping[ErrorCode::SUCCESS];
        $data['items'] = $items;
        $data['page'] = $this->page;
        $data['page_size'] = $this->page_size;
        $data['total'] = $this->total;
        $response['data'] = $data;
        $response['reload'] = $reload;
        $response['redirect'] = $redirect;
        $this->response($response);
    }

    protected function errorResponse($code = ErrorCode::FAILURE, $data = [], $reload = false, $redirect = '')
    {
        if (!empty($code) && is_integer($code)) {
            $response['code'] = $code;
            $response['message'] = ErrorCode::$error_code_mapping[$code];
            $data = empty($data) ? (object)[] : $data;
        } else {
            $response['code'] = ErrorCode::FAILURE;
            $response['message'] = $code;
            $response['data'] = $data;
        }
        $response['data'] = $data;
        $response['reload'] = $reload;
        $response['redirect'] = $redirect;
        $this->response($response);
    }

    protected function isGet()
    {
        return $this->request->method == 'GET' ? true : false;
    }

    protected function isPost()
    {
        return $this->request->method == 'POST' ? true : false;
    }

    protected function isAjax()
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            return true;
        }
    }

    protected function initQueryParams()
    {
        $this->params = array_merge($this->post(), $this->get());
        if (preg_match('/application\/json/', $this->server('CONTENT_TYPE'))) {
            $extraData = file_get_contents("php://input");
            $extraData = $extraData ? json_decode($extraData, true) : [];
            $this->params = array_merge($extraData, $this->params);
        }
        $this->page_size = $this->params['page_size'] ?? $this->page_size;
        $this->page = $this->params['page'] ?? $this->page;
        if (isset($this->params['filter_html']) && strlen($this->params['filter_html']) > 0) {
            $this->filter_html = $this->params['filter_html'] == 0 ? false : $this->filter_html;
        }
    }

    protected function server($name = null)
    {
        $data = !$name ? $_SERVER : (isset($_SERVER[$name]) ? $_SERVER[$name] : false);
        return $this->xssClean($data);
    }

    protected function get($name = '')
    {
        $data = !$name ? $_GET : (isset($_GET[$name]) ? $_GET[$name] : false);
        if (!empty($this->server['QUERY_STRING'])) {
            $query_strings = explode('&', $this->server['QUERY_STRING']);
            if (isset($data[$query_strings[0]])) {
                unset($data[$query_strings[0]]);
            }
        }
        return $this->xssClean($data);
    }

    protected function post($name = null)
    {
        $data = !$name ? $_POST : (isset($_POST[$name]) ? $_POST[$name] : false);
        return $this->xssClean($data);
    }

    protected function files($name = null)
    {
        $data = !$name ? $_FILES : (isset($_FILES[$name]) ? $_FILES[$name] : false);
        return $this->xssClean($data);
    }

    protected function cookie($name = '')
    {
        $data = !$name ? $_COOKIE : (isset($_COOKIE[$name]) ? $_COOKIE[$name] : false);
        return $data ? $this->xssClean($data) : [];
    }

    protected function xssClean($data)
    {
        if (is_array($data)) {
            $data = array_map(function ($v) {
                if (is_array($v)) {
                    return $v;
                }
                return htmlspecialchars($v);
            }, $data);
            return filter_var_array($data, FILTER_SANITIZE_STRING);
        } else {
            $data = htmlspecialchars($data);
            return filter_var($data, FILTER_SANITIZE_STRING);
        }
    }

    protected function htmlClean($data)
    {
        if (is_array($data)) {
            return array_map(function ($v) {
                if (is_array($v)) {
                    return $v;
                }
                return htmlspecialchars($v);
            }, $data);
        } else {
            return htmlspecialchars($data);
        }
    }

    protected static function logger()
    {
        return static::$logger ? static::$logger : Logger::getInstance();
    }
}
