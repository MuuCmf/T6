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

use app\common\controller\Base;
/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class Member extends Base
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
            //dump($uid);exit;
            if (0 < $uid) {
                $res = $commonMemberModel->login($uid); //登陆
                $this->success('注册成功', $step_url);
            } else { //注册失败，显示错误信息
                $this->error($commonMemberModel->getError());
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

            if ($uid > 0) {
                return $this->result(200,'登录成功');
            } else {
                return $this->result(0,'账号密码错误');
            }
        } 
    }

    /* 退出登录 */
    public function logout()
    {
        if (is_login()) {
            model('Member')->logout();
            $this->success(lang('_SUCCESS_LOGOUT_').lang('_EXCLAMATION_'), Url('index/Index/index'));
        } else {
            $this->redirect('member/login');
        }
    }

    /* 用户密码找回首页 */
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
                $uid = Db::name('UcenterMember')->where(['mobile' => $account])->value('id');
                break;
                case 'email':
                $uid = Db::name('UcenterMember')->where(['email' => $account])->value('id');
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

    private function getResetPasswordVerifyCode($uid)
    {
        $user = UCenterMember()->where(array('id' => $uid))->find();
        $clear = implode('|', array($user['uid'], $user['username'], $user['last_login_time'], $user['password']));
        $verify = muucmf_hash($clear, UC_AUTH_KEY);
        return $verify;
    }

    /**
     * 提示激活页面
     */
    public function activate()
    {
        $aUid = session('temp_login_uid');
        $status = Db::name('UcenterMember')->where(array('id' => $aUid))->value('status');
        if ($status != 3) {
            redirect(Url('ucenter/member/login'));
        }
        $info = query_user(array('uid', 'nickname', 'email'), $aUid);
        $this->assign($info);
        return $this->fetch();
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
     * changeEmail  更改邮箱
     */
    public function changeEmail()
    {
        $aEmail = input('post.email', '', 'text');
        $aUid = session('temp_login_uid');

        if (Db::name('UcenterMember')->where(['id' => $aUid])->value('status') != 3) {
            $this->error(lang('_ERROR_AUTHORITY_LACK_').lang('_EXCLAMATION_'));
        }
        Db::name('UcenterMember')->where(['id' => $aUid])->setField('email', $aEmail);
        clean_query_user_cache($aUid, 'email');
        $res = $this->activateVerify();
        $this->success(lang('_SUCCESS_CHANGE_'), 'refresh');
    }

    /**
     * activateVerify 添加激活验证
     * @return bool|string
     */
    private function activateVerify()
    {
        $aUid = session('temp_login_uid');
        $email = Db::name('UcenterMember')->where(['id' => $aUid])->value('email');
        $verify = model('Verify')->addVerify($email, 'email', $aUid);
        $res = $this->sendActivateEmail($email, $verify, $aUid); //发送激活邮件
        return $res;
    }

    /**
     * sendActivateEmail   发送激活邮件
     * @param $account
     * @param $verify
     * @return bool|string
     */
    private function sendActivateEmail($account, $verify, $uid)
    {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . Url('ucenter/member/doActivate?account=' . $account . '&verify=' . $verify . '&type=email&uid=' . $uid);
        $content = modC('REG_EMAIL_ACTIVATE', '{$url}', 'USERCONFIG');
        $content = str_replace('{$url}', $url, $content);
        $content = str_replace('{$title}', modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config'), $content);
        $res = send_mail($account, modC('WEB_SITE_NAME', lang('_MUUCMF_'), 'Config') . lang('_VERIFY_LETTER_'), $content);
        return $res;
    }

    /**
     * saveAvatar  保存头像
     */
    public function saveAvatar()
    {
        //跳回的地址
        $redirect_url = session('temp_login_uid') ? url('ucenter/member/step', ['step' => get_next_step('change_avatar')]) : url('ucenter/config/avatar');

        $aCrop = input('post.crop', '', 'text');
        $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
        $aPath = input('post.path', '', 'text');
		
        if (empty($aCrop)) {
            $this->success(lang('_SUCCESS_SAVE_').lang('_EXCLAMATION_'),$redirect_url );
        }
        $returnPath = controller('ucenter/UploadAvatar', 'widget')->cropPicture($aCrop,$aPath);

        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');

        //更新数据库数据
        $data = [
            'uid' => $aUid,
            'status' => 1, 
            'is_temp' => 0,
            'path' => $returnPath,
            'driver'=> $driver, 
            'create_time' => time()
        ];
        $res = Db::name('avatar')->where(['uid' => $aUid])->update($data);
        if (!$res) {
            Db::name('avatar')->insert($data);
        }
        clean_query_user_cache($aUid, array('avatars','avatars_html'));

        $this->success(lang('_SUCCESS_AVATAR_CHANGE_').lang('_EXCLAMATION_'), $redirect_url);
    }

    /**
     * doActivate  激活步骤
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doActivate()
    {
        $aAccount = input('get.account', '', 'text');
        $aVerify = input('get.verify', '', 'text');
        $aType = input('get.type', '', 'text');
        $aUid = input('get.uid', 0, 'intval');
        $check = model('Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if ($check) {
            set_user_status($aUid, 1);
            $this->success(lang('_SUCCESS_ACTIVE_'), Url('ucenter/member/step', array('step' => get_next_step('start'))));
        } else {
            $this->error(lang('_FAIL_ACTIVE_').lang('_EXCLAMATION_'));
        }
    }

    /**
     * checkAccount  ajax验证用户帐号是否符合要求
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
     * checkNickname  ajax验证昵称是否符合要求
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