<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/5
 * Time: 11:31
 */

namespace App\Library\Common;


class DingTalk
{
    static $web_hook = 'https://oapi.dingtalk.com/robot/send?access_token=63c054615f4ee5a0286fc327d9983622aa43087662a7a393eba3f9a0d8fce179';

    public static function sendTalk($talk)
    {
        $hostname = php_uname('n');
        if ($hostname == 'web-095') {
            $start_time = strtotime(date('Y-m-d 09:00:00', time()));
            $end_time = strtotime(date('Y-m-d 19:00:00', time()));
            if (time() > $start_time && time() < $end_time) {
                $message = [
                    'msgtype' => 'text',
                    'text' => ['content' => $talk],
                    'at' => ['isAtAll' => 'true']
                ];
                static::httpRequest(static::$web_hook, json_encode($message));
            }
        }
    }

    private static function httpRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $res;
    }
}