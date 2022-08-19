<?php
namespace app\api\controller;

use app\common\model\Member as MemberModel;
use app\common\model\Verify as VerifyModel;
use app\common\service\Mail;
use app\common\controller\Common;

/**
 * 验证码接口
 */
class Verify extends Common
{
    protected $MemberModel;
    protected $VerifyModel;
    
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->MemberModel = new MemberModel();
        $this->VerifyModel = new VerifyModel();
        $this->mailService = new Mail();
    }

    /**
     * sendVerify 发送验证码
     */
    public function send()
    {
        $account = $username = input('post.account', '', 'text');
        $type = input('post.type', 'mobile', 'text');
        $type = $type == 'mobile' ? 'mobile' : 'email';
        $type_str = $type == 'mobile' ? '手机号' : 'email';
        if (empty($account)) {
            return $this->error($type_str . '不能为空');
        }
        // 判断格式类型
        $check_email = preg_match("/[a-z0-9_\-\.]+@([a-z0-9_\-]+?\.)+[a-z]{2,3}/i", $account);
        $check_mobile = preg_match("/^(1[0-9])[0-9]{9}$/", $account);
        if($type == 'email' && !$check_email){
            return $this->error('邮箱格式错误');
        }
        if($type == 'mobile' && !$check_mobile){
            return $this->error('手机格式错误');
        }

        // 验证手机号码唯一性
        // $has_map = [
        //     ['shopid', '=', $this->shopid],
        // ];
        // if($type == 'mobile'){
        //     $has_map[] = ['mobile', '=', $account];
        // }
        // if($type == 'email'){
        //     $has_map[] = ['email', '=', $account];
        // }
        // $has_account = $this->MemberModel->where($has_map)->find();

        // if($has_account){
        //     return $this->error($type_str . '已绑定其他用户');
        // }

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

        // 写入验证码
        $verify = $this->VerifyModel->addVerify($account, $type);
        if (!$verify) {
            return $this->error('验证码写入失败');
        }
        
        // 发送验证码
        switch ($type) {
            case 'mobile':
                $res = $this->VerifyModel->sendSMS($account, $verify);
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
                $subject = config('system.WEB_SITE_NAME');
                $body = "您的验证码为{$verify}验证码，账号为{$account}。";
                
                $res = $this->mailService->sendMailLocal($account, $subject, $body);
                if($res == true){
                    session('verify_time', $time);
                    return $this->success('验证码发送成功');
                }
            break;
        }
        
        return $this->error($res);
    }
}