<?php
namespace app\ucenter\controller;

use think\App;
use think\facade\Request;
use think\facade\Session;
use think\facade\Db;
use think\facade\Cache;
use think\Response;
use think\Validate;
use think\exception\ValidateException;
use app\common\model\Member as CommonMember;
use app\common\model\ActionLimit;
use thans\jwt\facade\JWTAuth;
use app\common\controller\Base;

/**
 * 用户登录及注册
 */
class Common extends Base
{
    /**
     * register  注册页面
     */
    public function register()
    {
        //提交注册
        if (request()->isPost()) {
            
            //获取参数
            $account = input('post.account', '', 'text');
            $password = input('post.password', '', 'text');
            $confirm_password = input('post.confirm_password', '', 'text');
            $verify = input('post.verify', '', 'text');

            //注册开关设置
            if (!modC('REG_SWITCH', '', 'USERCONFIG')) {
                $this->error('注册功能临时关闭，请稍后访问！');
            }

            $ActionLimit = new ActionLimit();
            $return = $ActionLimit->checkActionLimit('reg', 'member', 1, 1, true);
           
            if ($return && !$return['code']) {
                $this->error($return['msg'], $return['url']);
            }

            //昵称注册开关
            if (modC('NICKNAME_SWITCH', 0, 'USERCONFIG') == 0) {
                $nickname = modC('NICKNAME_PREFIX','','USERCONFIG').$account;
            }else{
                $nickname = input('post.nickname', '', 'text');
            }

            //判断注册类型
            $check_email = preg_match("/[a-z0-9_\-\.]+@([a-z0-9_\-]+?\.)+[a-z]{2,3}/i", $account, $match_email);
            $check_mobile = preg_match("/^(1[0-9])[0-9]{9}$/", $account, $match_mobile);
            if ($check_email) {
                $email = $account;
                $username = '';
                $mobile = '';
            } elseif ($check_mobile) {
                $mobile = $account;
                $username = '';
                $email = '';
            } else {
                $username = $account;
                $mobile = '';
                $email = '';
            }

            $type = check_account_type($account);

            // 验证验证码
            if (($type == 'mobile' && modC('MOBILE_VERIFY_TYPE', 0, 'USERCONFIG') == 1) || 
                (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 1 && $type == 'email')) {
                if (!model('Verify')->checkVerify($account, $type, $verify, 0)) 
                {
                    $this->result(0,'验证码错误');
                }
            }

            /* 注册用户 */
            $commonMemberModel = new CommonMember;
            $uid = $commonMemberModel->register($username, $nickname, $password, $email, $mobile, $type);

            if (0 < $uid) {
                $token = JWTAuth::builder(['uid' => $uid]); //参数为用户认证的信息，请自行添加
                // 登录账号
                $res = $commonMemberModel->login($uid);

                return $this->result(200,'注册成功',$token);
            } else {
                //注册失败，显示错误信息
                 return $this->result(0,$commonMemberModel->getError());
            }
        }
    }

    /* 登录页面 */
    public function login()
    {
        if (request()->isPost()) {
             //获取参数
            $account = input('post.account', '', 'text');
            $password = input('post.password', '', 'text');

            // 验证账号和密码
            $commonMemberModel = new CommonMember;
            $uid = $commonMemberModel->verifyUserPassword($account, $password);

            //登录
            $res = $commonMemberModel->login($uid);

            if ($res) {
                $token = JWTAuth::builder(['uid' => $uid]); //参数为用户认证的信息，请自行添加
                return $this->result(200,'登录成功',$token);
            } else {
                return $this->result(0,$commonMemberModel->getError());
            }
        }
    }

    /* 用户密码找回 */
    public function mi()
    {
        if (request()->isPost()) {
            $account = $username= input('post.account','','text');
            $type = input('post.type','','text');
            $password = input('post.password','','text');
            $verify = input('post.verify',0,'intval');//验证码
            //传入数据判断
            if(empty($account) || empty($type) || empty($password) || empty($verify)){
                $this->error(lang('_EMPTY_CANNOT_'));
            }
            check_username($username, $email, $mobile, $aUnType);
            //检查验证码是否正确
            $ret = model('Verify')->checkVerify($account,$type,$verify,0);
            if(!$ret){//验证码错误
                $this->error(lang('_ERROR_VERIFY_CODE_'));
            }
            $resend_time =  modC('SMS_RESEND','60','USERCONFIG');
            if(time() > session('verify_time')+$resend_time ){//验证超时
                $this->error(lang('_ERROR_VERIFY_OUTIME_'));
            }
            //获取用户UID
            switch ($type) {
                case 'mobile':
                $uid = Db::name('Member')->where(['mobile' => $account])->value('id');
                break;
                case 'email':
                $uid = Db::name('Member')->where(['email' => $account])->value('id');
                break;
            }
            if (!$uid) {
                $this->error(lang('_ERROR_USED_1_') . lang('_USER_') . lang('_ERROR_USED_3_'));
            }
            //设置新密码
            $password = user_md5($password, config('database.user_auth'));
            $data['id'] = $uid;
            $data['password'] = $password;

            $ret = Db::name('UcenterMember')->update($data,['id'=>$uid]);
            if($ret){
                //返回成功信息前处理
                clean_query_user_cache($uid, 'password');//删除缓存
                Db::name('user_token')->where('uid=' . $uid)->delete();
                //返回数据
                $this->success(lang('_SUCCESS_SETTINGS_'), Url('Member/login'));
            }else{
                $this->error();
            }
        }
    }

    /**
     * reSend  重发邮件
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function reSend()
    {
        $res = $this->activateVerify();
        if ($res === true) {
            $this->success(lang('_SUCCESS_SEND_'), 'refresh');
        } else {
            $this->error(lang('_ERROR_SEND_') . $res, 'refresh');
        }
    }

    /**
     * 验证用户帐号是否符合要求
     */
    public function checkAccount()
    {
        $aAccount = input('post.account', '', 'text');
        $aType = input('post.type', '', 'text');
        if (empty($aAccount)) {
            $this->error(lang('_EMPTY_CANNOT_').lang('_EXCLAMATION_'));
        }
        check_username($aAccount, $email, $mobile, $aUnType);
        $mUcenter = new UcenterMember;
        switch ($aType) {
            case 'username':
                empty($aAccount) && $this->error(lang('_ERROR_USERNAME_FORMAT_').lang('_EXCLAMATION_'));
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < modC('USERNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')) {
                    $this->error(lang('_ERROR_USERNAME_LENGTH_1_').modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('USERNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_USERNAME_LENGTH_2_'));
                }


                $id = $mUcenter->where(array('username' => $aAccount))->value('id');
                if ($id) {
                    $this->error(lang('_ERROR_USERNAME_EXIST_2_'));
                }
                preg_match("/^[a-zA-Z0-9_]{".modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').",".modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')."}$/", $aAccount, $result);
                if (!$result) {
                    $this->error(lang('_ERROR_USERNAME_ONLY_PERMISSION_'));
                }
                break;
            case 'email':
                empty($email) && $this->error(lang('_ERROR_EMAIL_FORMAT_').lang('_EXCLAMATION_'));
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $this->error(lang('_ERROR_EMAIL_EXIST_'));
                }

                $id = $mUcenter->where(array('email' => $email))->value('id');
                if ($id) {

                    $this->error(lang('_ERROR_EMAIL_EXIST_'));
                }
                break;
            case 'mobile':
                empty($mobile) && $this->error(lang('_ERROR_PHONE_FORMAT_'));
                $id = $mUcenter->where(array('mobile' => $mobile))->value('id');
                if ($id) {
                    $this->error(lang('_ERROR_PHONE_EXIST_'));
                }
                break;
        }
        $this->success(lang('_SUCCESS_VERIFY_'));
    }

    /**
     * 验证昵称是否符合要求
     */
    public function checkNickname()
    {
        $aNickname = input('post.nickname', '', 'text');

        if (empty($aNickname)) {
            $this->error('昵称不能为空！');
        }

        $length = mb_strlen($aNickname, 'utf-8'); // 当前数据长度
        if ($length < modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG')) {
            $this->error(lang('_ERROR_NICKNAME_LENGTH_11_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_USERNAME_LENGTH_2_'));
        }

        $memberModel = model('member');
        $uid = $memberModel->where(['nickname' => $aNickname])->value('uid');
        if ($uid) {
            $this->error(lang('_ERROR_NICKNAME_EXIST_'));
        }
        preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $aNickname, $result);
        if (!$result) {
            $this->error(lang('_ERROR_NICKNAME_ONLY_PERMISSION_'));
        }

        $this->success(lang('_SUCCESS_VERIFY_'));
    }

}