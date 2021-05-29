<?php
namespace app\common\model;

use think\Model;
use think\Db;
use app\common\model\ActionLog;

/**
 * 会员模型
 */
class Member extends Model
{
    protected $autoWriteTimestamp = true;

    //自动完成
    protected $insert = ['reg_ip'];
    protected $update = ['update_time'];

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $nickname 昵称
     * @param  string $password 用户密码
     * @param  string $email 用户邮箱
     * @param  string $mobile 用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username='', $nickname, $password, $email='', $mobile='', $type=1)
    {
        $data = [
            'username' => $username,
            'password' => user_md5($password,config('database.auth_key')),
            'email' => $email,
            'mobile' => $mobile,
            'nickname' => $nickname,
            'type' => $type,
            'status' => 1,
        ];

        /*
        //验证器验证数据
        $validate = new \app\ucenter\validate\Member;
        //测试数据时可暂时禁用验证
        if(!$validate->check($data)){
            return $validate->getError();
        }*/

        /* 添加用户 */
        if ($res = $this->save($data)) {
            if (!$res) {
                return false;
            }else{
                $uid = $this->id;
                $actionLog = new ActionLog();
                $actionLog->actionLog('reg','member',1,$uid);
                return $uid;
            }
            
        } else {
            return -1; //错误详情见自动验证注释
        }
    }

    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function getUid($username, $password, $type = 1)
    {

        $map = [];
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }
        /* 获取用户数据 */
        $user = $this->get($map);

        $return = model('ActionLimit')->checkActionLimit('input_password','ucenter_member',$user['id'],$user['id']);

        if($return && !$return['code']){
            return $return['msg'];
        }

        if ($user['id'] && $user['status']) {
            /* 验证用户密码 */
            if (user_md5($password, config('database.auth_key')) === $user['password']) {
                return $user['id']; //返回用户ID
            } else {
                action_log('input_password','member',$user['id'],$user['id']);
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid)
    {
        
        /* 检测是否在当前应用注册 */
        $user = $this->where('uid',$uid)->find();
        if (1 != $user['status']) {
            $this->error = '用户已禁用'; //应用级别禁用
            return false;
        }
        
        /* 登录用户 */
        $this->autoLogin($user);
        //记录行为
        $actionLog = new ActionLog();
        $actionLog->actionLog('user_login', 'member', $uid, $uid);
       
        return true;
    }


    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    public function autoLogin($user)
    {
        /* 更新登录信息 */
        $data = [
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(1),
        ];

        $this->save($data,$user['uid']);
        $this->where(['uid'=>$user['uid']])->setInc('login');
        
        /* 记录登录SESSION和COOKIES */
        $auth = [
            'uid' => $user['uid'],
            'last_login_time' => $user['last_login_time'],
        ];

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
    }

    /**
     * 用户密码找回认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function lomi($username, $email)
    {
        $map = array();
        $map['username'] = $username;
        $map['email'] = $email;
        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if (is_array($user)) {
            /* 验证用户 */
            //if($user['last_login_time']){
            //return $user['last_login_time']; //成功，返回用户最后登录时间
            return $user; //成功，返回用户最后登录时间
            //}else{
            //return $user['reg_time']; //返回用户注册时间
            //return -1; //成功，返回用户最后登录时间
            //}
        } else {
            return -2; //用户和邮箱不符
        }
    }

    /**
     * 用户密码找回认证2
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function reset($uid)
    {
        $map = array();
        $map['id'] = $uid;
        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if (is_array($user)) {
            return $user; //成功，返回用户数据

        } else {
            return -2; //用户和邮箱不符
        }
    }

    /**
     * 根据IP获取用户最后注册时间
     * @param  string  $uid 用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function infos($regip)
    {
        $map['reg_ip'] = $regip;
        $user = $this->where($map)->max('reg_time');
        if ($user) {
            return $user;
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 获取用户信息
     * @param  string  $uid 用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false)
    {
        $map = array();
        if ($is_username) { //通过用户名获取
            $map['username'] = $uid;
        } else {
            $map['id'] = $uid;
        }

        $user = $this->where($map)->field('id,username,email,mobile,status')->find();
        if (is_array($user) && $user['status'] = 1) {
            return array($user['id'], $user['username'], $user['email'], $user['mobile']);
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    protected function updateLogin($uid)
    {
        $data = array(
            'id' => $uid,
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(1),
        );
        $this->update($data);
    }

    /**修改密码
     * @param $old_password
     * @param $new_password
     * @return bool
     */
    public function changePassword($old_password, $new_password ,$confirm_password)
    {
        //检查旧密码是否正确
        if (!$this->verifyUser(get_uid(), $old_password)) {
            //'旧密码错误';
            return -40;
        }

        $data = [
            'password' => $new_password,
            'confirm_password' =>$confirm_password,
        ];
        //验证密码
        $validate = new \app\ucenter\validate\UcenterMember;
        $result = $validate->scene('password')->check($data);;
        if(false === $result){
            return $validate->getError();
            return false;
        }
        //移除数组中无用值
        unset($data['confirm_password']);
        //$data = array_values($data);
        //密码数据加密
        $password = user_md5($new_password, Config('database.auth_key'));
        $data['password'] = $password;
        //更新用户信息
        $res = $this->save($data,['id' => get_uid()]);
        if($res){
            //返回成功信息
            clean_query_user_cache(get_uid(), 'password');//删除缓存
            Db::name('user_token')->where('uid','=',get_uid())->delete();
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 验证用户密码
     * @param int    $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     * @author huajie <banhuajie@163.com>
     */
    public function verifyUser($uid, $password_in)
    {
        $password = $this->getFieldById($uid, 'password');
        if (user_md5($password_in, config('database.auth_key')) === $password) {
            return true;
        }
        return false;
    }

    /**向ucenter_member表中写入数据并返回uid
     * @param string $prefix 数据前缀
     * @return mixed
     */
    public function addSyncData($prefix='')
    {
        $data['username'] = rand_username($prefix);
        $data['password'] = create_rand(10);
        $data['status'] = 1; //用户状态默认启用
        $data['type'] = 1;  // 视作用用户名注册
        $res = $this->save($data);
        $uid = $this->id; //获取自增ID
        return $uid;
    }

    protected  function rand_email()
    {
        $email = create_rand(10) . '@muucmf.cn';
        if ($this->where(['email' => $email])->select()) {
            $this->rand_email();
        } else {
            return $email;
        }
    }

}
