<?php
namespace app\ucenter\controller;

use think\App;
use think\facade\Session;
use think\facade\Db;
use think\facade\Cache;
use app\common\model\Verify as VerifyModel;
use app\common\controller\Common;

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
        dump($verify);exit;
        // 发送验证码
        switch ($type) {
            case 'mobile':
                //发送手机短信验证
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                //TODO:其它类型该版本暂不写，这里留个记号
                $res = $this->verifyModel->sendSMS($account, $verify);
            break;
            case 'email':
                //发送验证邮箱
                $res = $this->verifyModel->sendMail($account, $verify);
                //return $res;
            break;
        }
        
        if ($res === true) {
            if($type == 'mobile'){
                session('verify_time',$time);
            }
            $this->success('验证码发送成功');
        } else {
            $this->error($res);
        }

    }
}