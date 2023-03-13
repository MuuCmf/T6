<?php

use app\admin\model\AuthRule;
use think\facade\Db;
use muucmf\Auth;
use app\common\model\Member;
use app\common\model\MemberWallet;
use app\common\model\MemberSync;
use thans\jwt\exception\JWTException;
use thans\jwt\facade\JWTAuth;

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 大蒙 <59262424@qq.com>
 */
if (!function_exists('is_login')) {
    function is_login()
    {
        $header = request()->header();
        if (isset($header['authorization'])) {
            $token = JWTAuth::getToken();
            if (!empty($token)) {
                try {
                    $payload = JWTAuth::decode($token);
                    $uid = $payload['uid']->getValue();
                } catch (JWTException $exception) {
                    // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                    $uid = 0;
                }

                request()->uid = $uid;
            }
        }

        if (!empty(request()->uid)) {
            return request()->uid;
        }

        $user = session('user_auth');
        if (empty($user)) {
            return 0;
        } else {
            return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
        }
    }
}

/**
 * 获取用户UID（语义化）
 */
if (!function_exists('get_uid')) {
    function get_uid()
    {
        return is_login();
    }
}

/**
 * 获取用户openid
 */
if (!function_exists('get_openid')) {
    function get_openid($shopid = 0, $uid = 0, $channel = 'weixin_h5')
    {
        $model = new MemberSync();
        $map = [
            ['shopid', '=', $shopid],
            ['uid', '=', $uid],
            ['type', '=', $channel]
        ];
        $openid = $model->where($map)->value('openid');
        return $openid;
    }
}

/**
 * 获取用户数据
 */
if (!function_exists('query_user')) {
    function query_user($uid = 0, $field_arr = [])
    {
        if (empty($field_arr)) {
            $field = "*";
        }
        if (is_array($field_arr)) {
            $field = implode(',', $field_arr);
        }
        // 获取用户数据
        $memberModel = new Member;
        $auth_user = $memberModel->info($uid, $field);
        
        return $auth_user;
    }
}

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
if (!function_exists('get_username')) {
    function get_username($uid = 0)
    {
        $member = new Member();
        return $member->getUsername($uid);
    }
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
if (!function_exists('get_nickname')) {
    function get_nickname($uid = 0)
    {
        $member = new Member();
        return $member->getNickname($uid);
    }
}

/**
 * 获得具有某个权限节点的全部用户UID数组
 * @param string $rule
 */
if (!function_exists('get_auth_user')) {
    function get_auth_user($rule = '')
    {
        $rule = Db::name('AuthRule')->where('name', $rule)->find();
        $groups = Db::name('AuthGroup')->select();
        $uids = array();
        foreach ($groups as $v) {
            $auth_rule = explode(',', $v['rules']);
            if (in_array($rule['id'], $auth_rule)) {
                $gid = $v['id'];
                $temp_uids = (array) Db::name('AuthGroupAccess')->where(['group_id' => $gid])->column('uid');
                if ($temp_uids !== null) {
                    $uids = array_merge($uids, $temp_uids);
                }
            }
        }
        $uids = array_merge($uids, 1);
        $uids = array_unique($uids);

        return $uids;
    }
}

/**
 * 检测账号类型
 * @param  [type] $account [description]
 * @return [type]          [description]
 */
if (!function_exists('check_account_type')) {
    function check_account_type($account = '')
    {
        $check_email = preg_match("/[a-z0-9_\-\.]+@([a-z0-9_\-]+?\.)+[a-z]{2,3}/i", $account, $match_email);
        $check_mobile = preg_match("/^(1[0-9])[0-9]{9}$/", $account, $match_mobile);
        if ($check_email) {
            $type = 'email';
        } elseif ($check_mobile) {
            $type = 'mobile';
        } else {
            $username = $account;
            $type = 'username';
        }

        return $type;
    }
}

/**
 * check_username  根据type或用户名来判断注册使用的是用户名、邮箱或者手机
 * @param $username
 * @param $email
 * @param $mobile
 * @param int $type
 * @return bool
 */
if (!function_exists('check_username')) {
    function check_username(&$username, &$email, &$mobile, &$type = 'username')
    {

        if ($type) {
            switch ($type) {
                case 'email':
                    $email = $username;
                    $username = '';
                    $mobile = '';
                    $type = 'email';
                    break;
                case 'mobile':
                    $mobile = $username;
                    $username = '';
                    $email = '';
                    $type = 'mobile';
                    break;
                default:
                    $mobile = '';
                    $email = '';
                    $username = $username;
                    $type = 'username';
                    break;
            }
        } else {
            $check_email = preg_match("/[a-z0-9_\-\.]+@([a-z0-9_\-]+?\.)+[a-z]{2,3}/i", $username, $match_email);
            $check_mobile = preg_match("/^(1[0-9])[0-9]{9}$/", $username, $match_mobile);
            if ($check_email) {
                $email = $username;
                $username = '';
                $mobile = '';
                $type = 'email';
            } elseif ($check_mobile) {
                $mobile = $username;
                $username = '';
                $email = '';
                $type = 'mobile';
            } else {
                $mobile = '';
                $email = '';
                $type = 'username';
            }
        }
        return true;
    }
}

/**
 * 验证注册格式是否开启
 * @param $type
 * @return bool
 */
if (!function_exists('check_reg_type')) {
    function check_reg_type($type)
    {
        $t[1] = $t['username'] = 'username';
        $t[2] = $t['email'] = 'email';
        $t[3] = $t['mobile'] = 'mobile';

        $switch = config('system.USER_REG_SWITCH');
        if ($switch) {
            $switch = explode(',', $switch);
            if (in_array($t[$type], $switch)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * 验证登录提示信息是否开启
 * @param $type
 * @return bool
 */
if (!function_exists('check_login_type')) {
    function check_login_type($type)
    {
        $t[1] = $t['username'] = 'username';
        $t[2] = $t['email'] = 'email';
        $t[3] = $t['mobile'] = 'mobile';
        $t[4] = $t['qrcode'] = 'qrcode';

        $switch = config('system.USER_LOGIN_SWITCH');
        if ($switch) {
            $switch = explode(',', $switch);
            if (in_array($t[$type], $switch)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * 系统用户非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
if (!function_exists('user_md5')) {
    function user_md5($str, $key = '')
    {
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
}

/**
 * 图形验证码开关
 * @param  [type] $open [description]
 * @return [type]       [description]
 */
if (!function_exists('check_verify_open')) {
    function check_verify_open($open)
    {
        $config = config('system.VERIFY_OPEN');

        if ($config) {
            $config = explode(',', $config);
            if (in_array($open, $config)) {
                return true;
            }
        }
        return false;
    }
}


/**随机生成一个用户名
 * @param $prefix 前缀
 * @return string
 */
if (!function_exists('rand_username')) {
    function rand_username($prefix)
    {
        if (empty($prefix)) {
            $username = create_rand(10);
        } else {
            $username = $prefix . '_' . create_rand(10);
        }

        if (Db::name('member')->where(['username' => $username])->find()) {
            rand_username($prefix);
        } else {
            return $username;
        }
    }
}

/**
 * 随机生成一个用户昵称
 * @param      string  $prefix  The prefix
 * @return     <type>  ( description_of_the_return_value )
 */
if (!function_exists('rand_nickname')) {
    function rand_nickname($prefix)
    {
        if (empty($prefix)) {
            $nickname = create_rand(8);
        } else {
            $nickname = $prefix . '_' . create_rand(8);
        }

        if (Db::name('member')->where(['nickname' => $nickname])->find()) {
            rand_nickname($prefix);
        } else {
            return $nickname;
        }
    }
}

/**
 * 随机生成一个邮箱地址
 */
if (!function_exists('rand_email')) {
    function rand_email()
    {
        $email = create_rand(10) . '@muucmf.cn';
        if (Db::name('member')->where('email', $email)->count() > 0) {
            return rand_email();
        } else {
            return $email;
        }
    }
}

if (!function_exists('check_auth')) {
    function check_auth($rule = '', $except_uid = -1, $type = AuthRule::RULE_URL)
    {
        if (is_login() == 1) {
            return true; //管理员允许访问任何页面
        }
        if ($except_uid != -1) {
            if (!is_array($except_uid)) {
                $except_uid = explode(',', $except_uid);
            }
            if (in_array(is_login(), $except_uid)) {
                return true;
            }
        }
        $rule = empty($rule) ? strtolower(app('http')->getName()) . '/' . strtolower(request()->controller()) . '/' . strtolower(request()->action()) : $rule;
        // 检测是否有该权限
        if (!Db::name('auth_rule')->where(['name' => $rule, 'status' => 1])->find()) {
            return false;
        }

        static $Auth = null;
        if (!$Auth) {
            $Auth = new Auth();
        }

        if (!$Auth->check($rule, is_login(), $type)) {
            return false;
        }
        return true;
    }
}

/**
 * 获取当前的积分
 * @param string $score_name
 * @return mixed
 */
if (!function_exists('get_my_score')) {
    function get_my_score($score_name = 'score1')
    {
        $user = query_user(is_login(), array($score_name));
        $score = $user[$score_name];

        return $score;
    }
}
