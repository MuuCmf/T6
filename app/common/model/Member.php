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
    public function register($username='', $nickname, $password, $email='', $mobile='', $type='username')
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
                $actionLog->add('reg','member',1,$uid);
                return $uid;
            }
            
        } else {
            return -1;
        }
    }

    /**
     * 验证账号和密码是否正确
     * @param  string  $account 账号
     * @param  string  $password 用户密码
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function verifyUserPassword($account, $password)
    {
        $type = check_account_type($account);
        $map = [];
        switch ($type) {
            case 'username':
                $map['username'] = $account;
                break;
            case 'email':
                $map['email'] = $account;
                break;
            case 'mobile':
                $map['mobile'] = $account;
                break;
            case 'uid':
                $map['uid'] = $account;
                break;
            default:
                return 0; //参数错误
        }
        // 获取用户数据
        $user = $this->where($map)->find();
        // 行为限制
        $actionLimit = new ActionLimit();
        $return =$actionLimit->checkActionLimit('input_password','member',$user['uid'],$user['uid']);

        if($return && !$return['code']){
            return $return['msg'];
        }

        if ($user['uid'] && $user['status']) {
            /* 验证用户密码 */
            if (user_md5($password, config('database.auth_key')) === $user['password']) {
                return $user['uid']; //返回用户ID
            } else {
                $actionLog = new ActionLog();
                $actionLog->add('input_password','member',$user['uid'],$user['uid']);
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
    public function login(int $uid)
    {
        /* 检测是否在当前应用注册 */
        $user = $this->where('uid',$uid)->find();
        if (1 != $user['status']) {
            $this->error = '用户已禁用'; //应用级别禁用
            return false;
        }

        //更新登录信息
        $this->updateLogin($uid);
        
        //记录行为
        $actionLog = new ActionLog();
        $actionLog->add('user_login', 'member', $uid, $uid);

        return true;
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
            $map['uid'] = $uid;
        }

        $user = $this->where($map)->field('uid,username,email,mobile,status')->find();
        if (is_array($user) && $user['status'] = 1) {
            return [$user['id'], $user['username'], $user['email'], $user['mobile']];
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    public function updateLogin(int $uid)
    {
        $data = array(
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(1),
        );
        $this->where('uid',$uid)->save($data);
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
    
    public function randEmail()
    {
        $email = create_rand(10) . '@muucmf.cn';
        if ($this->where(['email' => $email])->select()) {
            $this->rand_email();
        } else {
            return $email;
        }
    }

    public function getNickname(int $uid)
    {
        //调用接口获取用户信息
        $nickname = $this->where('uid',$uid)->value('nickname');

        return $nickname;
    }
}
