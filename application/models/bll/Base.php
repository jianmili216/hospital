<?php
/**
 * Created by PhpStorm.
 * Это не будет в состоянии
 * User: Lee
 * Date: 2019-09-18
 * Time: 16:56
 */
namespace App\Model\Bll;

use App\Model\Dao\ForumDiary;
use App\Model\Dao\ForumThread;
use App\Utils\Logger;


class Base
{


    const PREFORM_TIME = 3;

    /**
     * 格式化时间
     * @param $time int 时间
     * @return bool|string $data string 格式化后的时间
     */
    public static function timeFormat($time)
    {
        $time = strtotime($time);
        $time_day=date('d',$time);

        #判断昨天 （23:59:59）
        $oneDay= strtotime(date('Y-m-d',$time))+172800;
        #判断前天（23:59:59）
        $twoDay= strtotime(date('Y-m-d',$time))+259200;

        $now_time = time();
        $now_day=date('d',time());

        $postedTime = ($now_time - $time);

        #1天之内
        if($postedTime <= 86400){
            if($now_day == $time_day){
                if ($postedTime < 60) {
                    $date = '刚刚';
                } elseif ($postedTime < 3600) { // 1小时内
                    $date = floor($postedTime / 60) . '分钟前';
                } elseif ($postedTime < 21600) {//六小时内
                    $date = floor($postedTime / 3600) . '小时前';
                }else{
                    $date = '今天' . date("H:i", $time);
                }
            }else{
                $date = '昨天' . date("H:i", $time);
            }
        }elseif($postedTime > 86400 && $postedTime <= 172800){
            #超过1天到2天之内
            if($now_time < $oneDay){
                $date = '昨天' . date("H:i", $time);
            }else{
                $date = '前天' . date("H:i", $time);
            }
        }else{
            #超过2天
            if($now_time < $twoDay){
                $date = '前天' . date("H:i", $time);
            }else{
                $date = date("Y-m-d H:i", $time);
            }
        }
        return $date;
    }

    /**
     * 格式化时间
     * @return xx年xx月xx日
     */
    public static function dayFormat($time)
    {
        return date('Y年m月d日', strtotime($time));
    }


    /**
     * 格式化数字
     * description：格式化大于1W的数值
     * @param $data int 数值
     * @return $data int 格式化后的数值
     */
    public static function numberFormat($data)
    {

        if($data>9999){
            return ceil($data/10000).'w+';
        }
        return $data;
    }


    /**
     * 返回今日开始和结束的时间
     * @return array
     */
    public static function today()
    {
        return [
            date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y'))),
            date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')))
        ];
    }


    /**
     * 返回昨日开始和结束的时间
     * @return array
     */
    public static function yesterday()
    {
        $yesterday = date('d') - 1;
        return [
            date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), $yesterday, date('Y'))),
            date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), $yesterday, date('Y')))
        ];
    }


    /**
     * 返回本周开始和结束的时间
     * @return array
     */
    public static function week()
    {
        $timestamp = time();
        return [
            date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)))),
            date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))) + 24 * 3600 - 1)
        ];
    }


    // 年龄转换
    public static function getAge($birthday)
    {
        $age = strtotime($birthday);
        if ($age === false) {
            return false;
        }
        list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
        $now = strtotime("now");
        list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
        $age = $y2 - $y1;
        if ((int)($m2 . $d2) < (int)($m1 . $d1)) {
            $age -= 1;
        }
        return $age;
    }


    //备孕字典转换
    public static function getPhaseRelation($id, $time = '')
    {
        $allow_id = [1, 3, 4];
        $baby_allow_id = [7,8,9,10,11,12];
        $diary_list = ForumThread::$diary_phase_mapping;
        $glad_list = ForumThread::$glad_phase_mapping;
        $phase_list = array_merge($diary_list, $glad_list);
        $phase = '';
        foreach ($phase_list as $value) {
            if ($value['id'] == $id) {
                $phase = $value['value'];
            }
        }
        if (in_array($id, $allow_id) && $time) {
            $day = '-' . ceil((time() - strtotime($time)) / 86400) . '天';
            $phase .= $day;
        }
        if (in_array($id, $baby_allow_id)) {
            $phase = '试管中-'.$phase;
        }
        return $phase;
    }


    //天气字典转换
    public static function getWeatherRelation($id)
    {
        $data = ForumDiary::$weather_mapping;
        foreach ($data as $value) {
            if ($value['id'] == $id) {
                return $value;
            }
        }
        return ForumDiary::$weather_mapping[0];
    }


    // 二维数组根据key排序
    public static function arraySort($arr, $keys, $type = 'desc')
    {
        $keys_value = $new_array = array();
        foreach ($arr as $k => $v) {
            $keys_value[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keys_value) : arsort($keys_value);
        reset($keys_value);
        foreach ($keys_value as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    // 二维数组根据某个元素去重
    public static function arrayColumnUni($arr, $key)
    {
        $res = array();
        foreach ($arr as $value) {
            if (isset($res[$value[$key]])) {
                unset($value[$key]);
            } else {
                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }

    public static function emojiToUnicodeStr($utf8)
    {
        $return = '';
        for ($i = 0; $i < mb_strlen($utf8); $i++) {
            $char = mb_substr($utf8, $i, 1);
            if (strlen($char) > 3) {
                $char = trim(json_encode($char), '"');
            }
            $return .= $char;
        }
        return $return;
    }

    public static function stringToHtml($contents)
    {
        $contents = htmlspecialchars_decode($contents, ENT_QUOTES);
        $temp = array_map(function($v) {
            return trim($v);
        }, explode(PHP_EOL, $contents));
        $contents = implode("</br>", $temp);
        $contents = str_replace('"', '\'', $contents);
        $contents = str_replace('&nbsp;', chr(32), $contents);
        return $contents;
    }

    public static function filterHtml($contents, $filter_html = true,$is_retain=false)
    {
        if($is_retain) {
            $contents= $filter_html ? strip_tags(static::stringToHtml($contents), "<br> <img> <p>") : static::stringToHtml($contents);
        }else{
            $contents= $filter_html ? strip_tags(static::stringToHtml($contents)) : static::stringToHtml($contents);
            $contents = str_replace('&nbsp;', chr(32), $contents);
        }
        return $contents;

    }

    public static function performTime($start_time, $end_time, $keyword)
    {
        $perform_time = $end_time - $start_time;
        if($perform_time >= self::PREFORM_TIME){
            $message = $keyword .'的执行时间大于 ' . $perform_time . '秒';
            Logger::getInstance('application')->save($message, 'error');
        }
    }

    public static function generateTree($rows, $pid = 0)
    {
        $tree = [];
        foreach ($rows as $v) {
            if (!isset($v['parent_id'])) {
                $tree[] = $v;
            } elseif ($v['parent_id'] == $pid) {
                $v['children'] = self::generateTree($rows, $v['id']);
                if (empty($v['children'])) unset($v['children']);
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public static function removeEmoji($str)
    {
        if (empty($str)) {
            return '';
        }
        return preg_replace("#(\\\ud[0-9a-f]{3})#i", "", $str);
    }

    public static function removeLinks($str)
    {
        if (empty($str)) {
            return '';
        }
        $str = preg_replace('/(http)(.)*([a-z0-9\-\.\_])+/i', '', $str);
        $str = preg_replace('/(www)(.)*([a-z0-9\-\.\_])+/i', '', $str);
        return $str;
    }
}