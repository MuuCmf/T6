<?php
namespace app\ucenter\validate;

use think\Validate;
use think\facade\Db;

class Member extends Validate
{
    //需要验证的键值
    protected $rule =   [
        'username'           => "checkUsername|checkUsernameLength|checkDenyMember|unique:member",
        'email'              => "email|checkDenyEmail|unique:member",  //验证邮箱|验证邮箱在表中唯一性
        'mobile'             => "mobile|checkDenyMobile|unique:member",
        'password'           => 'require|length:6,32',
        'confirm_password'   => 'require|length:6,32|confirm:password',
    ];

    //验证不符返回msg
    protected $message  =   [
        'username.unique'           => '用户名已存在',
        'email.email'               => '邮箱格式错误',
        'email.unique'              => '邮箱已经存在',
        'mobile.unique'             => '手机号已存在',
        'mobile.mobile'             => '手机格式错误',
        'password.require'          => '密码不能为空',
        'password.length'           => '密码长度应在6 - 32位之间',
        'confirm_password.require'  => '确认密码不能为空',
        'confirm_password.confirm'  => '两次输入的密码不匹配',  
    ];

    // 自定义验证规则
    /**
     * 验证用户名长度
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function checkUsernameLength($value)
    {
        $length = mb_strlen($value, 'utf-8'); // 当前数据长度
        if ($length < config('system.USER_USERNAME_MIN_LENGTH') || $length > config('system.USER_USERNAME_MAX_LENGTH')) {
            return '用户名长度应该在'. config('system.USER_USERNAME_MIN_LENGTH') . '~' . config('system.USER_USERNAME_MAX_LENGTH') . '之间';
        }
        return true;
    }

    /**
     * 检查用户名格式
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function checkUsername($value)
    {
        //如果用户名中有空格，不允许注册
        if (strpos($value, ' ') !== false) {
            return false;
        }
        preg_match("/^[a-zA-Z0-9_]{0,64}$/", $value, $result);

        if (!$result) {
            return -12;
        }
        return true;
    }

    /**
     * 检测用户名是不是被禁止注册(保留用户名)
     * @param  string $username 用户名
     * @return boolean          ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMember($value)
    {
        $denyName = Db::name("Config")->where(['name' => 'USER_NAME_BAOLIU'])->value('value');
        if($denyName!=''){
            $denyName = explode(',',$denyName);
            foreach($denyName as $val){
                if(!is_bool(strpos($value,$val))){
                    return '用户名已被系统禁止注册';
                }
            }
        }
        return true;
    }

    /**
     * 检测邮箱是不是被禁止注册
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyEmail($value)
    {
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /**
     * 检测手机是不是被禁止注册
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMobile($value)
    {
        return true; //TODO: 暂不限制，下一个版本完善
    }

 }   