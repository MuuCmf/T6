<?php
namespace app\ucenter\controller;

use think\facade\Db;
use think\facade\View;
use app\common\model\Member as CommonMember;
use app\ucenter\validate\Member;
use think\exception\ValidateException;
use app\common\model\ActionLimit;
use thans\jwt\facade\JWTAuth;
use app\common\model\Verify;
use app\common\controller\Common as CommonCommon;
use EasyWeChat\Factory;

/**
 * 用户登录及注册
 */
class Common extends CommonCommon
{
    /**
     * register  注册页面
     */
    public function register()
    {
        //提交注册
        if (request()->isPost()) {
            
            //获取参数
            $account = input('post.account', '', 'text'); // 账号
            $password = input('post.password', '', 'text'); // 密码
            $confirm_password = input('post.confirm_password', '', 'text'); // 确认密码
            $verify = input('post.verify', '', 'text'); // 邮件或手机验证码
            $captcha = input('post.captcha', '', 'text'); // 图形验证码
            $agreement = input('post.agreement', 0, 'intval'); // 用户服务协议勾选状态
            $forward = input('forward', '/index/index/index', 'text'); // 来源页面
            
            //注册开关设置
            if (!config('system.USER_REG_SWITCH')) {
                return $this->error('注册功能临时关闭，请稍后访问！');
            }

            // 账号为空验证
            if(empty($account)){
                return $this->error('账号不能为空');
            }

            // 行为限制验证
            // $ActionLimit = new ActionLimit();
            // $return = $ActionLimit->checkActionLimit('reg', 'member', 1, 1, true);
            // if ($return && !$return['code']) {
            //     return $this->error($return['msg'], $return['url']);
            // }

            //昵称注册开关
            if (config('system.USER_NICKNAME_SWITCH') == 0) {
                $nickname = rand_nickname(config('system.USER_NICKNAME_PREFIX'));
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
            // 自动获取注册类型
            $type = check_account_type($account);
            // 判断注册类型是否启用
            if(check_reg_type($type) == false){
                return $this->error('未启用的注册类型或输入格式错误！');
            }
            // 验证
            try {
                validate(Member::class)->check([
                    'username'  => $username,
                    'email' => $email,
                    'mobile' => $mobile,
                    'password' => $password,
                    'confirm_password' => $confirm_password,
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }

            // 检测图形验证码
            if (check_verify_open('reg')) {
                if (!captcha_check($captcha)) {
                    return $this->error('图形验证码错误');
                }
            }

            // 验证验证码
            if (($type == 'mobile') || $type == 'email') {
                $verifyModel = new Verify();
                if (!$verifyModel->checkVerify($account, $type, $verify)) {
                    return $this->error('验证码错误');
                }
            }
            
            // 验证是否勾选了协议
            if(empty($agreement)){
                return $this->error('请勾选用户服务协议');
            }
            /* 注册用户并写入数据 */
            $commonMemberModel = new CommonMember;
            $uid = $commonMemberModel->register($username, $nickname, $password, $email, $mobile, $type);

            if (0 < $uid) {
                $token = JWTAuth::builder(['uid' => $uid]); //参数为用户认证的信息，请自行添加
                // 登录账号
                $commonMemberModel->login($uid);
                // 返回成功
                return $this->success('恭喜您！注册成功。',$token, $forward);
            } else {
                //注册失败，显示错误信息
                 return $this->error($commonMemberModel->getError());
            }
        }else{
            // 注册类型开关
            $regSwitch = config('system.USER_REG_SWITCH');
            if(!empty($regSwitch)){
                $regSwitch = explode(',',$regSwitch);
            }
            View::assign('regSwitch', $regSwitch);
            // 昵称开关
            $nicknameSwitch = config('system.USER_NICKNAME_SWITCH');
            View::assign('nicknameSwitch', $nicknameSwitch);

            $this->setTitle('注册');
            return View::fetch();
        }
    }

    /**
     *  登录 
    */
    public function login()
    {
        if (request()->isPost()) {
            //获取参数
            $account = input('post.account', '', 'text'); // 账号
            $password = input('post.password', '', 'text'); // 密码
            $captcha = input('post.captcha', '', 'text'); // 短信验证码
            $captcha_pic = input('post.captcha_pic', '', 'text'); // 图形验证码
            $login_type = input('post.login_type','password');//登录类型
            $remember = input('post.remember', 0, 'intval');

            if(empty($account)) return $this->error('账号不能为空');
            $commonMemberModel = new CommonMember;

            if ($login_type == 'password'){
                //密码登录
                if(empty($password)) return $this->error('密码不能为空');
                // 检测图形验证码
                if (check_verify_open('login')) {
                    if (!captcha_check($captcha_pic)) {
                        return $this->error('图形验证码错误');
                    }
                }
                // 验证账号和密码
                $uid = $commonMemberModel->verifyUserPassword($account, $password);
                if($uid == -1) return $this->error('用户不存在或被禁用');
                if($uid == -2) return $this->error('密码错误');
            }else{
                //验证码登录
                $uid = $commonMemberModel->verifyUserCaptcha($account, $captcha);
                if($uid == -2) return $this->error('验证码错误');
                //快捷登录，第一次登录的用户默认生成新用户
                if($uid == -1) {
                    $type = check_account_type($account);
                    if($type == 'email'){
                        $email = $account;
                        $mobile = '';
                    }else{
                        $email = '';
                        $mobile = $account;
                    }
                    $uid = $commonMemberModel->randMember('' ,'','',$email,$mobile);
                }
            }

            //登录
            $res = $commonMemberModel->login($uid, $remember);
            if ($res) {
                $last_url = session('login_http_referer');
                if(empty($last_url)){
                    $last_url = request()->domain();
                }
                $token = JWTAuth::builder(['uid'=>$uid]);
                $token = 'Bearer ' . $token;

                return $this->success('登录成功',['token' => $token],$last_url);
            } else {
                return $this->error($commonMemberModel->getError());
            }
        }else{
            // 允许的登录类型
            $ph = $ph_account = [];
            check_login_type('username') && $ph[] = '用户名';
            check_login_type('email') && $ph_account[] = $ph[] = '邮箱';
            check_login_type('mobile') && $ph_account[] = $ph[] = '手机';
            View::assign([
                'ph' => implode('/', $ph),
                'ph_account' => implode('/', $ph_account)
            ]);

            return View::fetch();
        }
    }

    /**
     * 快捷登陆
     */
    public function quickLogin()
    {
        // 允许的登录类型
        $ph = $ph_account = [];
        check_login_type('username') && $ph[] = '用户名';
        check_login_type('email') && $ph_account[] = $ph[] = '邮箱';
        check_login_type('mobile') && $ph_account[] = $ph[] = '手机';
        View::assign([
            'ph' => implode('/', $ph),
            'ph_account' => implode('/', $ph_account)
        ]);

        // 输出页面
        return View::fetch();
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        if(is_login()){
            $commonMemberModel = new CommonMember;
            $commonMemberModel->logout(is_login());
            return $this->success('退出成功','', request()->domain());
        } else {
            return $this->error('error');
        }
    }

    /**
     * 用户密码找回
     */
    public function mi()
    {
        if (request()->isPost()) {
            $account = $username= input('post.account','','text');
            $type = input('post.type','','text');
            $password = input('post.password','','text');
            $verify = input('post.verify',0,'intval');//验证码
            //传入数据判断
            if(empty($account) || empty($type) || empty($password) || empty($verify)){
                return $this->error('数据不能为空');
            }
            check_username($username, $email, $mobile, $aUnType);
            //检查验证码是否正确
            $verifyModel = new Verify();
            $ret = $verifyModel->checkVerify($account,$type,$verify,0);
            if(!$ret){//验证码错误
                return $this->error('验证码错误');
            }
            $resend_time =  config('system.SMS_RESEND');
            if(time() > session('verify_time') + $resend_time ){//验证超时
                return $this->error('验证码超时');
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
                return $this->error('用户不存在');
            }
            //设置新密码
            $password = user_md5($password, config('database.user_auth'));
            $data['id'] = $uid;
            $data['password'] = $password;

            $ret = Db::name('Member')->update($data,['id'=>$uid]);
            if($ret){
                //返回成功信息前处理
                // clean_query_user_cache($uid, 'password');//删除缓存
                Db::name('user_token')->where('uid=' . $uid)->delete();
                //返回数据
                return $this->success('操作成功', url('Member/login'));
            }else{
                return $this->error('操作失败');
            }
        }
    }

    /**
     * 验证用户帐号是否符合要求接口
     */
    public function checkAccount()
    {
        $aAccount = input('post.account', '', 'text');
        $aType = input('post.type', '', 'text');
        if (empty($aAccount)) {
            $this->error('账号不能为空');
        }
        check_username($aAccount, $email, $mobile, $aType);
        
        $commonModel = new CommonMember;
        switch ($aType) {
            case 'username':
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < config('system.USER_USERNAME_MIN_LENGTH') || $length > config('system.USER_USERNAME_MAX_LENGTH')) {
                    return $this->error('用户名长度不在'.config('system.USER_USERNAME_MIN_LENGTH').'-'.config('system.USER_USERNAME_MAX_LENGTH'). '之间');
                }
                $id = $commonModel->where(['username' => $aAccount])->value('uid');
                if ($id) {
                    return $this->error('用户名已存在');
                }
                preg_match("/^[a-zA-Z0-9_]{".config('system.USER_USERNAME_MIN_LENGTH').",".config('system.USER-USERNAME_MAX_LENGTH')."}$/", $aAccount, $result);
                if (!$result) {
                    return $this->error('用户名仅允许字母、数字和下划线');
                }
                break;
            case 'email':
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                preg_match("/[a-z0-9_\-\.]+@([a-z0-9_\-]+?\.)+[a-z]{2,3}/i", $email, $match_email);
                if(!$match_email){
                    return $this->error('邮箱格式错误');
                }
                $res = $commonModel->where('email', '=', $email)->value('uid');
                if ($res) {
                    return $this->error('邮箱已存在');
                }
                break;
            case 'mobile':
                preg_match("/^(1[0-9])[0-9]{9}$/", $mobile, $match_mobile);
                if(!$match_mobile){
                    return $this->error('手机格式错误');
                }
                $res = $commonModel->where('mobile', '=', $mobile)->value('uid');
                if ($res) {
                    return $this->error('手机号已存在');
                }
                break;
        }
        return $this->success('验证通过');
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
        if ($length < config('system.USER_NICKNAME_MIN_LENGTH') || $length > config('system.USER_NICKNAME_MAX_LENGTH')) {
            $this->error('昵称长度在' .config('system.USER_NICKNAME_MIN_LENGTH').'-'.config('system.USER_NICKNAME_MAX_LENGTH'). '之间');
        }

        $memberModel = model('member');
        $uid = $memberModel->where(['nickname' => $aNickname])->value('uid');
        if ($uid) {
            $this->error(lang('_ERROR_NICKNAME_EXIST_'));
        }
        preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $aNickname, $result);

        if (!$result) {
            $this->error('只允许中文、字母和数字和下划线');
        }

        return $this->success('验证成功');
    }

    /**
     * 用户服务协议显示页
     */
    public function agreement()
    {
        $agreement = config('system.USER_REG_AGREEMENT');
        View::assign('agreement', $agreement);
        return View::fetch();
    }

}