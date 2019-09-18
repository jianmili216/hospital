<?php
/**
 * Created by PhpStorm.
 * User: andyweijin
 * Date: 17/12/27
 * Time: 下午8:32
 * @param $data
 */

function p($data)
{
    echo "<pre>";
    print_r($data);
}

function jp($json)
{
    p(json_decode($json, true));
}

function d($data)
{
    echo "<pre>";
    var_dump($data);
    exit();
}

function getRemoteIP()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($pos = strpos($ip, ',')) {
        $ip = substr($ip, 0, $pos);
    }
    return $ip;
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return false;
    } else {
        $error_msg = 'SYSTEM ERROR: ' . $errstr . " Fatal error on line $errline in file $errfile";
        throw new Exception($error_msg, 501);
    }
}

function isList($arr)
{
    if (is_array($arr)) {
        if (empty($arr)) {
            return true;
        } else if (array_keys($arr) === range(0, count($arr) - 1)) {
            return true;
        }
    }
    return false;
}


function isDict($arr)
{
    return is_array($arr) && !isList($arr) ? true : false;
}

function listIntersectKey($list, $tpl)
{
    $tpl = array_fill_keys($tpl, '');
    foreach ($list as $index => $item) {
        $list[$index] = array_intersect_key($item, $tpl);
    }
    return $list;
}

function getSpecificIndexFromList($list, $property, $value)
{
    $result = false;
    foreach ($list as $index => $item) {
        if (isset($item[$property]) && $item[$property] === $value) {
            $result = $index;
            break;
        }
    }
    return $result;
}

function getSpecificItemFromList($list, $property, $value)
{
    $result = [];
    foreach ($list as $item) {
        if (isset($item[$property]) && $item[$property] === $value) {
            $result = $item;
            break;
        }
    }
    return $result;
}

function getObjProperty($obj, $property, $default)
{
    if (isset($obj[$property])) {
        return isset($obj[$property]) ? $obj[$property] : $default;
    } else if (is_object($obj)) {
        return property_exists($obj, $property) ? $obj->$property : $default;
    }

    return $default;
}

function curl_get($url, &$httpCode = 0)
{
    //初始化
    $ch = curl_init();
    //传入url
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //不做证书校验，部署Linux环境下改为true
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $file_contents = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $file_contents;
}

function curl_post($url, array $params = array())
{
    sleep(1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_TIMEOUT,intval(10));
    $curl_result=curl_exec($ch);

    $error_data=array();
    if(!curl_errno($ch)){
        #参数错误
        if(!empty($curl_result)){
            $curl_result=json_decode($curl_result,true);
            if($curl_result['code'] != 200){
                $error_data['type_error']=2;
                $error_data['request_data']=json_encode($params,JSON_UNESCAPED_UNICODE);
                $error_data['response_code']=$curl_result['code'];
                $error_data['response_body']=$curl_result['message'];
            }
        }else{
            $error_data['type_error']=3;
            $error_data['request_data']=json_encode($params,JSON_UNESCAPED_UNICODE);
            $error_data['response_code']=curl_errno($ch);
            $error_data['response_body']='请求成功，未知错误，请检查参数！';
        }
    }else {
        #请求错误
        $error_data['type_error']=1;
        $error_data['request_data']=json_encode($params,JSON_UNESCAPED_UNICODE);
        $error_data['response_code']=curl_errno($ch);
        $error_data['response_body']='请求错误';
    }
    curl_close($ch);
    if(!empty($error_data)){
        return json_encode($error_data);
    }
}

function isMobile()
{
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_pc      = strpos($user_agent, 'windows nt') ? true : false;
    $is_mac     = strpos($user_agent, 'mac os') ? true : false;
    $is_iphone  = strpos($user_agent, 'iphone') ? true : false;
    $is_android = strpos($user_agent, 'android') ? true : false;
    $is_ipad    = strpos($user_agent, 'ipad') ? true : false;
    if ($is_pc) {
        return false;
    }
    if ($is_mac && !$is_iphone && !$is_ipad) {
        return false;
    }
    if ($is_iphone) {
        return true;
    }
    if ($is_android) {
        return true;
    }
    if ($is_ipad) {
        return true;
    }
}

/**
 * 递归实现无限极分类排序(权限列表)
 * @param $data 权限数据
 * @param $pid 父ID
 * @param $level 分类级别
 * @return $list 分好类的数组 直接遍历即可 $level可以用来遍历缩进
 */
function sortAuth($data, $pid = 0, $level = 0)
{
    static $list = [];
    foreach ($data as $k => $v) {
        # code...
        //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
        if($v['pid'] == $pid)
        {
            $v['level'] = $level;
            //把数组放到list中
            $list[] = $v;
            //把这个节点从数组中移除,减少后续递归消耗
            unset($data[$k]);
            //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
            sortAuth($data, $v['id'], $level+1);
        }
    }
    return $list;
}