<?php
use app\admin\model\AuthRule;
use think\facade\Cache;
use think\facade\Db;
use muucmf\Auth;
use think\captcha\Captcha;
use app\common\model\Member;

/**
 * 根据用户ID获取用户名
 * @param  integer $uid 用户ID
 * @return string       用户名
 */
function get_username($uid = 0)
{
    $member = new Member();
    return $member->getUsername($uid);
}

/**
 * 根据用户ID获取用户昵称
 * @param  integer $uid 用户ID
 * @return string       用户昵称
 */
function get_nickname($uid = 0)
{
    $member = new Member();
    return $member->getNickname($uid);
}

function check_auth($rule = '', $except_uid = -1, $type = AuthRule::RULE_URL)
{
    if (is_administrator()) {
        return true;//管理员允许访问任何页面
    }
    if ($except_uid != -1) {
        if (!is_array($except_uid)) {
            $except_uid = explode(',', $except_uid);
        }
        if (in_array(is_login(), $except_uid)) {
            return true;
        }
    }
    $rule = empty($rule) ? MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME : $rule;
    // 检测是否有该权限
    if (!Db::name('auth_rule')->where(array('name' => $rule, 'status' => 1))->find()) {
        return false;
    }
   static $Auth = null;
    if (!$Auth) {
        $Auth = new Auth();
    }

    if (!$Auth->check($rule, get_uid(), $type)) {
        return false;
    }
    return true;
}

/**获得具有某个权限节点的全部用户UID数组
 * @param string $rule
 */
function get_auth_user($rule = '')
{
    $rule = Db::name('AuthRule')->where(array('name' => $rule))->find();
    $groups = Db::name('AuthGroup')->select();
    $uids = array();
    foreach ($groups as $v) {
        $auth_rule = explode(',', $v['rules']);
        if (in_array($rule['id'], $auth_rule)) {
            $gid = $v['id'];
            $temp_uids =(array) Db::name('AuthGroupAccess')->where(['group_id' => $gid])->getField('uid');
            if ($temp_uids !== null) {
                $uids = array_merge($uids, $temp_uids);
            }
        }
    }
    $uids = array_merge($uids, get_administrator());
    $uids = array_unique($uids);
    return $uids;
}

/**
 * 检测账号类型
 * @param  [type] $account [description]
 * @return [type]          [description]
 */
function check_account_type($account)
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

/**
 * check_reg_type  验证注册格式是否开启
 * @param $type
 * @return bool
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function check_reg_type($type){
    $t[1] = $t['username'] ='username';
    $t[2] = $t['email'] ='email';
    $t[3] = $t['mobile'] ='mobile';

    $switch = modC('REG_SWITCH','','USERCONFIG');
    if($switch){
        $switch = explode(',',$switch);
        if(in_array($t[$type],$switch)){
           return true;
        }
    }
    return false;
}

/**
 * check_login_type  验证登录提示信息是否开启
 * @param $type
 * @return bool
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function check_login_type($type){
    $t[1] = $t['username'] ='username';
    $t[2] = $t['email'] ='email';
    $t[3] = $t['mobile'] ='mobile';

    $switch = modC('LOGIN_SWITCH','username','USERCONFIG');
    if($switch){
        $switch = explode(',',$switch);
        if(in_array($t[$type],$switch)){
            return true;
        }
    }
    return false;

}

/**
 * @param $content
 * @return mixed
 */
function match_users($content)
{
    $user_pattern = "/\@([^\#|\s]+)\s/"; //匹配用户
    preg_match_all($user_pattern, $content, $user_math);
    return $user_math;
}

/**
 * 系统用户非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function user_md5($str, $key = '')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 验证码开关
 * @param  [type] $open [description]
 * @return [type]       [description]
 */
function check_verify_open($open)
{
    $config = config('VERIFY_OPEN');

    if ($config) {
        $config = explode(',', $config);
        if (in_array($open, $config)) {
            return true;
        }
    }
    return false;
}

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 */
function check_verify($code, $id = 1)
{
    $captcha = new Captcha();
    return $captcha->check($code, $id);
}

/**
 * 生成图片验证码
 * @return [type] [description]
 */
function get_verify()
{
    captcha_src();
}

/**随机生成一个用户名
 * @param $prefix 前缀
 * @return string
 */
function rand_username($prefix = 'muu')
{
    $username = $prefix.'_'.create_rand(10);
    if (Db::name('member')->where(['username' => $username])->select()) {
        rand_username($prefix);
    } else {
        return $username;
    }
}

/**
 * 随机生成一个用户昵称
 * @param      string  $prefix  The prefix
 * @return     <type>  ( description_of_the_return_value )
 */
function rand_nickname($prefix = 'muu')
{
    $nickname = $prefix.'_'.create_rand(8);
    if (Db::name('member')->where(['nickname' => $nickname])->select()) {
        rand_nickname($prefix);
    } else {
        return $nickname;
    }
}