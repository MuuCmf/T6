<?php
namespace app\api\controller;

use app\common\model\Verify as VerifyModel;
use app\common\controller\Common;

/**
 * 验证码接口
 */
class Verify extends Common
{
    protected $verifyModel;
    
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->verifyModel = new VerifyModel();
    }

    /**
     * sendVerify 发送验证码
     */
    public function send()
    {
        $account = $username = input('post.account', '', 'text');
        $type = input('post.type', 'mobile', 'text');
        $type = $type == 'mobile' ? 'mobile' : 'email';
        if (empty($account)) {
            return $this->error('账号不能为空');
        }
        // 自动判断发送类型
        check_username($username, $email, $mobile, $type);
        $time = time();
        if($type == 'mobile'){
            //短信验证码的有效期，默认60秒
            $resend_time =  config('extend.SMS_RESEND');
            if($time <= session('verify_time') + $resend_time ){
                return $this->error('请' . ($resend_time-($time-session('verify_time'))). '秒后再发');
            }
        }

        if ($type == 'email' && empty($email)) {
            return $this->error('邮箱不能为空');
        }
        if ($type == 'mobile' && empty($mobile)) {
            return $this->error('手机号不能为空');
        }

        // 写入验证码
        $verify = $this->verifyModel->addVerify($account, $type);
        if (!$verify) {
            return $this->error('验证码写入失败');
        }
        
        // 发送验证码
        switch ($type) {
            case 'mobile':
                $res = $this->verifyModel->sendSMS($account, $verify);
                $smsDriver = config('extend.SMS_SEND_DRIVER');
                // 通过阿里云发送短信
                if($smsDriver == 'aliyun'){
                    if(is_array($res) && $res['Message'] == 'OK'){
                        session('verify_time', $time);
                        return $this->success('验证码发送成功');
                    }
                }
                // 通过腾讯云发送短信
                // array:2 [
                //     "SendStatusSet" => array:1 [
                //       0 => array:7 [
                //         "SerialNo" => "2433:204780860416317002348678043"
                //         "PhoneNumber" => "+8618618380435"
                //         "Fee" => 1
                //         "SessionContext" => ""
                //         "Code" => "Ok"
                //         "Message" => "send success"
                //         "IsoCode" => "CN"
                //       ]
                //     ]
                //     "RequestId" => "750b9dcf-0a96-4251-84f8-c554a1cd4760"
                //   ]
                if($smsDriver == 'tencent'){
                    if(is_array($res) && $res['SendStatusSet'][0]['Code'] == 'Ok'){
                        session('verify_time', $time);
                        return $this->success('验证码发送成功');
                    }
                }
            break;
            case 'email':
                //发送验证邮箱
                $res = $this->verifyModel->sendMail($account, $verify);
                //return $res;
            break;
        }
        
        return $this->error($res);
    }
}