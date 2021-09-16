<?php

namespace app\common\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    /**
     * 用常规方式发送邮件。
     */
    public function sendMailLocal($to = '', $subject = '', $body = '', $name = '', $attachment = null)
    {
        $from_email = config('system.MAIL_SMTP_USER');
        $from_name = config('systme.WEB_SITE_NAME');

        if(config('system.MAIL_SMTP_SSL')){
            $ssl_value = 'ssl';
        }else{
            $ssl_value = '';
        }
        
        $mail = new PHPMailer(true); //实例化PHPMailer

        try {
            $mail->isSMTP(); // 设定使用SMTP服务
            $mail->SMTPDebug = 0; // 关闭SMTP调试功能// 1 = errors and messages// 2 = messages only
            $mail->SMTPAuth = true; // 启用 SMTP 验证功能
            $mail->SMTPSecure = $ssl_value; // 使用安全协议
            $mail->Host = config('system.MAIL_SMTP_HOST'); // SMTP 服务器
            $mail->Port = config('system.MAIL_SMTP_PORT'); // SMTP服务器的端口号
            $mail->Username = config('system.MAIL_SMTP_USER'); // SMTP服务器用户名
            $mail->Password = config('system.MAIL_SMTP_PASS'); // SMTP服务器密码
            $mail->CharSet = 'UTF-8';// 设置发送的邮件的编码
            $mail->From = $from_email;// 设置发件人邮箱地址 同登录账号
            $mail->FromName = $from_name;

            if ($to == '') {
                $to = config('system.MAIL_SMTP_CE'); //邮件地址为空时，默认使用后台默认邮件测试地址
            }
            if ($name == '') {
                $name = config('system.WEB_SITE_NAME'); //发送者名称为空时，默认使用网站名称
            }
            if ($subject == '') {
                $subject = config('system.WEB_SITE_NAME'); //邮件主题为空时，默认使用网站标题
            }
            if ($body == '') {
                $body = config('system.WEB_SITE_NAME'); //邮件内容为空时，默认使用网站描述
            }
            $mail->isHTML(true);// 邮件正文是否为html编码 注意此处是一个方法
            $mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
            $mail->Subject = $subject;
            $mail->MsgHTML($body);    //发送的邮件内容主体
            $mail->addAddress($to, $name);
            if (is_array($attachment)) { // 添加附件
                foreach ($attachment as $file) {
                    is_file($file) && $mail->addAttachment($file);
                }
            }

            $status = $mail->send(); //? true : $mail->ErrorInfo; //返回错误信息
            
            if($status) {
                return true;
            }else{
                return $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        
        
    }

}