<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 9:45
 */
namespace App\Library\Common;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Utils\ConfigLoader;
use App\Library\Response\ErrorCode;

class Mailer
{

    protected $code_len = 4;
    protected $charset = '0123456789';

    const MAIL_CODE_EXPIRE = 900;


    public function sendMail($email_addresses, $attachment,$subject = '站点数据统计')
    {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            //$mail->SMTPDebug = 2;
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = ConfigLoader::get('mail.host');
            $mail->SMTPAuth = ConfigLoader::get('mail.smtpAuth');
            $mail->Username = ConfigLoader::get('mail.username');
            $mail->Password = ConfigLoader::get('mail.password');
            $mail->SMTPSecure = ConfigLoader::get('mail.smtpSecure');
            $mail->Port = ConfigLoader::get('mail.port');

            //Recipients
            $mail->setFrom(ConfigLoader::get('mail.username'), ConfigLoader::get('mail.fromname'));
            foreach ($email_addresses as $value){
                $mail->addAddress($value);
            }
            if(!empty($attachment)){
                foreach ($attachment as $val){
                    if(is_file($val)){
                        $mail->addAttachment($val);
                    }
                }
            }
            //Content
            $message = '请接收附件查看';
            $body = static::template($message);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            if (!$mail->send()) {
                return ['error_code' => ErrorCode::MAIL_SEND_FAILURE];
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
    }

    public static function template($message)
    {
        return <<<EOT
<html>
<head>
    <base target="_blank">
    <style type="text/css">
        ::-webkit-scrollbar {
            display: none;
        }
    </style>
    <style id="cloudAttachStyle" type="text/css">
        #divNeteaseBigAttach, #divNeteaseBigAttach_bak {
            display: none;
        }
    </style>
</head>
<body tabindex="0" role="listitem">
<style>
    .email-body {
        color: #40485B;
        font-size: 14px;
        font-family: -apple-system, "Helvetica Neue", Helvetica, "Nimbus Sans L", "Segoe UI", Arial, "Liberation Sans", "PingFang SC", "Microsoft YaHei", "Hiragino Sans GB", "Wenquanyi Micro Hei", "WenQuanYi Zen Hei", "ST Heiti", SimHei, "WenQuanYi Zen Hei Sharp", sans-serif;
        background: #f8f8f8
    }
    .pull-right {
        float: right
    }
    a {
        color: #FE7300;
        text-decoration: underline
    }
    a:hover {
        color: #fe9d4c
    }
    a:active {
        color: #b15000
    }
    .logo {
        text-align: center;
        margin-bottom: 20px
    }
    .panel {
        background: #fff;
        border: 1px solid #E3E9ED;
        margin-bottom: 10px
    }
    .panel-header {
        font-size: 18px;
        line-height: 30px;
        padding: 10px 20px;
        background: #fcfcfc;
        border-bottom: 1px solid #E3E9ED
    }
    .panel-body {
        padding: 20px
    }
    .container {
        width: 100%;
        max-width: 600px;
        padding: 20px;
        margin: 0 auto
    }
    .text-center {
        text-align: center
    }
    .thumbnail {
        padding: 4px;
        max-width: 100%;
        border: 1px solid #E3E9ED
    }
    .btn-primary {
        color: #fff;
        font-size: 16px;
        padding: 8px 14px;
        line-height: 20px;
        border-radius: 2px;
        display: inline-block;
        background: #FE7300;
        text-decoration: none
    }
    .btn-primary:hover, .btn-primary:active {
        color: #fff
    }
    .footer {
        color: #9B9B9B;
        font-size: 12px;
        margin-top: 40px
    }
    .footer a {
        color: #9B9B9B
    }
    .footer a:hover {
        color: #fe9d4c
    }
    .footer a:active {
        color: #b15000
    }
    .email-body#mail_to_teacher {
        line-height: 26px;
        color: #40485B;
        font-size: 16px;
        padding: 0px
    }
    .email-body#mail_to_teacher .container, .email-body#mail_to_teacher .panel-body {
        padding: 0px
    }
    .email-body#mail_to_teacher .container {
        padding-top: 20px
    }
    .email-body#mail_to_teacher .textarea {
        padding: 32px
    }
    .email-body#mail_to_teacher .say-hi {
        font-weight: 500
    }
    .email-body#mail_to_teacher .paragraph {
        margin-top: 24px
    }
    .email-body#mail_to_teacher .paragraph .pro-name {
        color: #000000
    }
    .email-body#mail_to_teacher .paragraph.link {
        margin-top: 32px;
        text-align: center
    }
    .email-body#mail_to_teacher .paragraph.link .button {
        background: #4A90E2;
        border-radius: 2px;
        color: #FFFFFF;
        text-decoration: none;
        padding: 11px 17px;
        line-height: 14px;
        display: inline-block
    }
    .email-body#mail_to_teacher ul.pro-desc {
        list-style-type: none;
        margin: 0px;
        padding: 0px;
        padding-left: 16px
    }
    .email-body#mail_to_teacher ul.pro-desc li {
        position: relative
    }
    .email-body#mail_to_teacher ul.pro-desc li::before {
        content: '';
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: red;
        position: absolute;
        left: -15px;
        top: 11px;
        background: #40485B
    }
    .email-body#mail_to_teacher .blackboard-area {
        height: 600px;
        padding: 40px;
        background-image: url(https://gitee.com/wewin11235/upload-gitee-image/raw/master/bg.jpg);
        color: #FFFFFF
    }
    .email-body#mail_to_teacher .blackboard-area .big-title {
        font-size: 32px;
        line-height: 45px;
        text-align: center
    }
    .email-body#mail_to_teacher .blackboard-area .desc {
        margin-top: 8px
    }
    .email-body#mail_to_teacher .blackboard-area .desc p {
        margin: 0px;
        text-align: center;
        line-height: 28px
    }
    .email-body#mail_to_teacher .blackboard-area .card:nth-child(odd) {
        float: left;
        margin-top: 45px
    }
    .email-body#mail_to_teacher .blackboard-area .card:nth-child(even) {
        float: right;
        margin-top: 45px
    }
    .email-body#mail_to_teacher .blackboard-area .card .title {
        font-size: 18px;
        text-align: center;
        margin-bottom: 10px
    }
</style>
<div class="email-body">
    <div class="container">
        <div class="logo">
            <img alt="Logo-black" src="https://static.tm51.com/image/LOGO.png" height="30">
        </div>
        <div class="panel">
            <div class="panel-header">
                通知
            </div>
            <div class="panel-body">
                <p>
                    您好!
                </p>
                <p>
                    {$message}
                </p>
                <p>
                    感谢您的关注，您也可以通过如下二维码下载我们论坛APP进行访问：
                </p>
                <div>
                    <img src="https://static.tm51.com/image/APP%E4%BA%8C%E7%BB%B4%E7%A0%81.png" alt="">
                </div>
            </div>
        </div>
        <div class="footer">
            <a href="https://www.tm51.com">www.tm51.com</a>
            <div class="pull-right">
                如果您有任何问题请联系QQ:89155438
            </div>
        </div>
    </div>
</div>
<br><br>
<div style="width:1px;height:0px;overflow:hidden">
    <img style="width:0;height:0" alt="" src="http://sctrack.sc.gg/track/open/eyJtYWlsbGlzdF9pZCI6IDAsICJ0YXNrX2lkIjogIiIsICJlbWFpbF9pZCI6ICIxNTM0NzczNzI5NTcxXzQyMTI5XzExMDJfMjkwMy5zYy0xMF85XzRfNDgtaW5ib3VuZDAkMTUxMDcxNzA0ODVAMTYzLmNvbSIsICJzaWduIjogImU5OTEyYjg0YzYwODhlYjZkNTRiOGM1YzQxMjdjYWZhIiwgInVzZXJfaGVhZGVycyI6IHt9LCAibGFiZWwiOiAwLCAidXNlcl9pZCI6IDQyMTI5LCAiY2F0ZWdvcnlfaWQiOiAxMjIyNjh9.gif">
</div>
<style type="text/css">
    body {
        font-size: 14px;
        font-family: arial, verdana, sans-serif;
        line-height: 1.666;
        padding: 0;
        margin: 0;
        overflow: auto;
        white-space: normal;
        word-wrap: break-word;
        min-height: 100px
    }
    td, input, button, select, body {
        font-family: Helvetica, 'Microsoft Yahei', verdana
    }
    pre {
        white-space: pre-wrap;
        white-space: -moz-pre-wrap;
        white-space: -pre-wrap;
        white-space: -o-pre-wrap;
        word-wrap: break-word;
        width: 95%
    }
    th, td {
        font-family: arial, verdana, sans-serif;
        line-height: 1.666
    }
    img {
        border: 0
    }
    header, footer, section, aside, article, nav, hgroup, figure, figcaption {
        display: block
    }
    blockquote {
        margin-right: 0px
    }
</style>
<style id="ntes_link_color" type="text/css">a, td a {
    color: #064977
}</style>
</body>
</html>
EOT;
    }

}