<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/18
 * Time: 14:13
 */
namespace App\Library\Common;

use App\Library\Response\ErrorCode;
use App\Model\Bll\BAd;
use App\Model\Bll\BImages;
use App\Utils\ConfigLoader;
use OSS\OssClient;
use OSS\Core\OssException;

class AliOSS
{

    protected $ossClient;
    private $allow_ext = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf'];

    const FILE_MAX_SIZE = 10485760;//10M

    const OSS_IMAGE_INFO = '?x-oss-process=image/info';

    const SMALL_WATERMARK = '/small_forum';
    const MIDDLE_WATERMARK = '/middle_forum';
    const BIG_WATERMARK = '/forum';
    const SMALL_WATERMARK_MAX_WIDTH = 200;
    const MIDDLE_WATERMARK_MAX_WIDTH = 400;


    public function __construct()
    {
        $accessKeyId = ConfigLoader::get('ali.oss.accessKeyId');
        $accessKeySecret = ConfigLoader::get('ali.oss.accessKeySecret');
        $endpoint = ConfigLoader::get('ali.oss.endpoint');

        try {
            // true为开启CNAME。CNAME是指将自定义域名绑定到存储空间上。
            // https://tmapp-static.tm51.com
            $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }


    /**
     * 简单图片上传 - $_FILES
     * @param $type string 广告：ad 头像：avatar 帖子：forum
     * @param $file array 临时文件 $_FILES
     * @param $uid int 用户id 上传用户头像时需要
     * @return array
     */
    public function putImageObject($type, $file, $uid)
    {
        if (empty($file)) return ['error_code' => ErrorCode::NO_FILES];
        if ($file['size'] > self::FILE_MAX_SIZE) return ['error_code' => ErrorCode::UPLOAD_FILE_SIZE_ASTRICT];
        $ext = $this->getFileExt($file['name']);
        //检测文件是否合法
        $res = $this->checkExt($ext);
        if ($res == false) {
            return ['error_code' => ErrorCode::UPLOAD_FILE_ERROR];
        }
        //文件存储位置+文件名
        if (!empty($uid) && $type == 'avatar') {
            $uid = sprintf("%09d", $uid);
            $dir1 = substr($uid, 0, 3);//一级目录
            $dir2 = substr($uid, 3, 2);//二级目录
            $dir3 = substr($uid, 5, 2);//三级目录
            $object = 'avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) .time(). "_avatar_big.jpg";
        } elseif ($type == 'ad') {
            $object = 'cd/' . date('Y') . date('m') . '/' . date('d') . '/' . md5(date('Ymd')) . rand(100000, 999999) . '.' . $ext;
        } else {
            $object = $type . '/' . date('Y') . date('m') . '/' . date('d') . '/' . md5(date('Ymd')) . rand(100000, 999999) . '.' . $ext;
        }
        try {
            $up_res = $this->ossClient->uploadFile(ConfigLoader::get('ali.oss.bucket'), $object, $file['tmp_name']);
            if (!empty($up_res)) {
                $info = $this->getImageInfo($up_res['info']['url']);
                if ($type == 'ad') {
                    $image['file_name'] = $file['name'];
                    $image['file_size'] = $file['size'];
                    $image['width'] = $info['width'] ?: 0;
                    $image['height'] = $info['height'] ?: 0;
                    $image['attachment'] = $object;
                    $img_id = BAd::putAd($image);
                } else {
                    $image['file_name'] = $file['name'];
                    $image['file_size'] = $file['size'];
                    $image['width'] = $info['width'] ?: 0;
                    $image['height'] = $info['height'] ?: 0;
                    $image['attachment'] = $object;
                    $img_id = BImages::putImage($image);
                }
                //水印规格 (仅限帖子加水印)
                if ($type == 'forum' && strtolower($ext) != 'gif') {
                    if ($info['width'] <= self::SMALL_WATERMARK_MAX_WIDTH) {
                        $object .= self::SMALL_WATERMARK;
                    } elseif ($info['width'] > self::SMALL_WATERMARK_MAX_WIDTH && $info['width'] <= self::MIDDLE_WATERMARK_MAX_WIDTH) {
                        $object .= self::MIDDLE_WATERMARK;
                    } else {
                        $object .= self::BIG_WATERMARK;
                    }
                }
                $data = [
                    'id' => $img_id,
                    'file_name' => $file['name'],
                    'path' => $object,
                    'url' => STATIC_IMAGE_PATH . $object,
                ];
                return $data;
            } else {
                return ['error_code' => ErrorCode::UPLOAD_FILE_FAILURE];
            }
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    public function getImageInfo($img)
    {
        $info = $this->httpRequest($img . self::OSS_IMAGE_INFO);
        if ($info) {
            $result['width'] = $info['ImageWidth']['value'];
            $result['height'] = $info['ImageHeight']['value'];
            return $result;
        }
        return $info;
    }

    private function getFileExt($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    private function checkExt($ext)
    {
        return in_array(strtolower($ext), $this->allow_ext);
    }

    private function httpRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code == 200) {
            return json_decode($res, true);
        }
        return false;
    }
}