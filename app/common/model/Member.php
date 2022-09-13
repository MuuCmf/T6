<?php
namespace app\common\model;

use app\admin\model\AuthGroup;
use app\common\model\ActionLog;
use think\Exception;
use think\Model;
use think\facade\Db;
use think\facade\Config;

/**
 * 会员模型
 */
class Member extends Model
{
    public $error;
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
     * @return integer 注册成功-用户信息，注册失败-错误编号
     */
    public function register($username='', $nickname = '', $password = '', $email='', $mobile='', $type='username')
    {
        $data = [
            'username' => $username,
            'password' => user_md5($password,Config::get('auth.auth_key')),
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
    public function verifyUserPassword($account ,$password)
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
        if($user){
            // 行为限制
            $actionLimit = new ActionLimit();
            $return = $actionLimit->checkActionLimit('input_password','member',$user['uid'],$user['uid']);
            if($return && !$return['code']){
                return $return['msg'];
            }
            
            if ($user['uid'] && $user['status']) {
                /* 验证用户密码 */
                if (user_md5($password, Config::get('auth.auth_key')) === $user['password']) {
                    return $user['uid']; //返回用户ID
                } else {
                    $actionLog = new ActionLog();
                    $actionLog->add('input_password','member',$user['uid'],$user['uid']);
                    return -2; //密码错误
                }
            }
        }

        return -1; //用户不存在或被禁用
    }

    /**
     * 验证账号和验证码是否正确
     * @param  string  $account 账号
     * @param  string  $captcha 验证码
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function verifyUserCaptcha($account ,$captcha)
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
        $verifyModel = new Verify();
        if($user){
            // 行为限制
            $actionLimit = new ActionLimit();
            $return = $actionLimit->checkActionLimit('input_password','member',$user['uid'],$user['uid']);

            if($return && !$return['code']){
                return $return['msg'];
            }

            if ($user['uid'] && $user['status']) {
                /* 验证用户验证码 */
                if (!$verifyModel->checkVerify($account, $type, $captcha)) {
                    return -2;
                }else{
                    return $user['uid']; //返回用户ID
                }
            }
        }else{
            if (!$verifyModel->checkVerify($account, $type, $captcha)) {
                return -2;
            }
        }
        return -1; //用户不存在或被禁用
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login(int $uid, int $remember = 0)
    {
        if($uid )
            /* 检测是否在当前应用注册 */
            $user = $this->where('uid','=',$uid)->find();

        if ($user['status'] !== 1) {
            $this->error = '用户已禁用'; //应用级别禁用
            return false;
        }

        //更新登录信息
        $this->updateLogin($uid);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid' => $user['uid'],
            'username' => $user['username'],
            'last_login_time' => $user['last_login_time'],
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

        //记录行为
        $actionLog = new ActionLog();
        $actionLog->add('user_login', 'member', $uid, $uid);
        //记住登录
        if ($remember == 1) {
            $token = Db::name('user_token')->where('uid', $uid)->value('token');
            if (empty($token)) {
                $data_token['uid'] = $uid;
                $token = create_unique();
                $data_token['token'] = $token;
                $data_token['create_time'] = time();
                
                Db::name('user_token')->insert($data_token);
            }
        }
        
        if (!$this->getCookieUid() && $remember) {
            $expire = 3600 * 24 * 7;
            cookie('MUU_LOGGED_USER', think_encrypt("{$uid}.{$token}",'muucmf', $expire));
        }

        return true;
    }

    public function getCookieUid()
    {
        static $cookie_uid = null;
        if (isset($cookie_uid) && $cookie_uid !== null) {
            return $cookie_uid;
        }else{
            $cookie = cookie('MUU_LOGGED_USER');
            if(!empty($cookie)){
                $cookie = explode(".", think_decrypt($cookie, 'muucmf'));
                $map['uid'] = $cookie[0];
                $user = Db::name('user_token')->where($map)->find();
                $cookie_uid = ($cookie[1] != $user['token']) ? false : $cookie[0];
                $cookie_uid = $user['create_time'] - time() >= 3600 * 24 * 7 ? false : $cookie_uid;//过期时间7天
            }
        }
        	
        return $cookie_uid;
    }

    /**
     * 记住登陆状态
     * @return [type] [description]
     */
    public function rembemberLogin()
    {
        if(!is_login()){
            //判断COOKIE
            $uid = $this->getCookieUid();
            if ($uid) {
                $this->login($uid);
                return $uid;
            }
        }
    }

    /**
     * 退出登录
     */
    public function logout(int $uid)
    {
        session(null);
        cookie('MUU_LOGGED_USER', NULL);

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
    public function info($uid, $fields = '*')
    {
        if(!empty($uid)){
            if(!empty($fields) && $fields != '*'){
                if(!is_array($fields)){
                    $fields_arr = explode(',', $fields);
                }else{
                    $fields_arr = $fields;
                }
                if(!in_array('uid',$fields_arr)){
                    array_push($fields_arr, 'uid');
                }

                if(!in_array('status',$fields_arr)){
                    array_push($fields_arr, 'status');
                }

                // 转回字符串
                $fields = implode(',', $fields_arr);
            }else{
                $fields = '*';
            }

            // 查询用户数组
            $map['uid'] = $uid;
            $member = $this->where($map)->field($fields)->find();
            if($member){
                $member = $member->toArray();
            }

            if (is_array($member) && $member['status'] = 1) {

                if($fields == '*' || strpos($fields, 'avatar') !== false){
                    // 头像
                    if(empty($member['avatar'])){
                        $member['avatar'] = $member['avatar64'] = $member['avatar128'] = $member['avatar256'] = $member['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
                    }else{
                        $member['avatar'] = get_attachment_src($member['avatar']);
                        $member['avatar64'] = get_thumb_image($member['avatar'], 64, 64);
                        $member['avatar128'] = get_thumb_image($member['avatar'], 128, 128);
                        $member['avatar256'] = get_thumb_image($member['avatar'], 256, 256);
                        $member['avatar512'] = get_thumb_image($member['avatar'], 512, 512);
                    }
                }

                if($fields == '*' || strpos($fields, 'balance') !== false){
                    // 余额
                    if (isset($member['balance'])){
                        $member['balance'] = sprintf("%.2f",$member['balance'] / 100);
                    }
                }

                // 扩展字段
                $field_group = Db::name('field_group')->where('status', '=', 1)->select()->toArray();
                $field_group_ids = array_column($field_group, 'id');
                $map_profile[] = ['group_id', 'in', $field_group_ids];
                $map_profile[] = ['status', '=', 1];
                $fields_list = Db::name('field_setting')->where($map_profile)->field('id,field_name,form_type')->select()->toArray();
                $fields_list = array_combine(array_column($fields_list, 'field_name'), $fields_list);
                $map_field['uid'] = $member['uid'];

                foreach ($fields_list as $key => $val) {
                    $map_field['field_id'] = $val['id'];
                    $field_data = Db::name('field')->where($map_field)->field('field_data')->find();
                    if ($field_data == null || $field_data == '') {
                        $member[$key] = '';
                    }
                    $member[$key] = $field_data;
                }

                return $member;

            } else {
                return -1; //用户不存在或被禁用
            }
        }else{
            return false;
        }
    }

    /**
     * 更新用户登录信息
     * @param  integer $uid 用户ID
     */
    public function updateLogin(int $uid)
    {
        $user = $this->where('uid','=',$uid)->find()->toArray();

        $data = [
            'login' => $user['login'] + 1,
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(),
        ];

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
            $this->error = '旧密码错误';
            return false;
        }

        $data = [
            'password' => $new_password,
            'confirm_password' =>$confirm_password,
        ];
        //验证密码
        $validate = new \app\ucenter\validate\Member;
        $result = $validate->scene('password')->check($data);
        if(false === $result){
            $this->error = $validate->getError();
            return false;
        }
        //移除数组中无用值
        unset($data['confirm_password']);
        //$data = array_values($data);
        //密码数据加密
        $password = user_md5($new_password, Config::get('auth.auth_key'));
        $data['password'] = $password;
        //更新用户信息
        $res = $this->where('uid', get_uid())->save($data);
        if($res){
            //返回成功信息
            return true;
        }else{
            $this->error = '密码修改失败';
            return false;
        }
    }

    /**
     * 随机生成一个邮箱地址
     */
    public function randEmail()
    {
        $email = create_rand(10) . '@muucmf.cn';
        if ($this->where('email',$email)->count() > 0) {
            return $this->randEmail();
        } else {
            return $email;
        }
    }

    /**
     * 获取用户名
     */
    public function getUsername(int $uid)
    {
        //调用接口获取用户信息
        $username = $this->where('uid',$uid)->value('username');

        return $username;
    }

    /**
     * 获取昵称
     */
    public function getNickname(int $uid)
    {
        //调用接口获取用户信息
        $nickname = $this->where('uid',$uid)->value('nickname');

        return $nickname;
    }

    /**
     * 验证昵称
     * @param $nickname
     */
    public function checkNickname($nickname, $uid)
    {
        $length = mb_strlen($nickname, 'utf8');
        try {
            if ($length == 0) {
                throw new Exception('请输入昵称');
            } else if ($length > Config::get('system.NICKNAME_MAX_LENGTH',32)) {
                throw new Exception('昵称不能超过'. Config::get('system.NICKNAME_MAX_LENGTH',32).'个字');
            } else if ($length < Config::get('system.NICKNAME_MIN_LENGTH',2)) {
                throw new Exception('昵称不能少于' . Config::get('system.NICKNAME_MIN_LENGTH',2) . '个字');
            }
            
            $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
            if (!$match) {
                throw new Exception('昵称只允许中文、字母、下划线和数字');
            }
            //验证唯一性
            $map_nickname[] = ['nickname', '=', $nickname];
            $map_nickname[] = ['uid','<>', $uid];
            $had_nickname = $this->where($map_nickname)->count();
            if ($had_nickname > 0) {
                throw new Exception('昵称已被人使用');
            }
            //保留昵称
            $denyName = Config::get('system.USER_NAME_BAOLIU');
            
            if (!empty($denyName)) {
                $denyName = explode(',', $denyName);
                foreach ($denyName as $val) {
                    if (!is_bool(strpos($nickname, $val))) {
                        throw new Exception('该昵称已被禁用');
                    }
                }
            }

            return true;
        }catch (Exception $e){
            return $e->getMessage();
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
        $password = $this->where('uid', $uid)->value('password');
        if (user_md5($password_in, Config::get('auth.auth_key')) === $password) {
            return true;
        }
        return false;
    }

    /**
     * 授权
     */
    public function oauth($data){
        $syncModel = new MemberSync();
        //是否已有授权信息
        $sync = $syncModel->where('openid',$data['openid'])->find();
        if ($sync){
            $uid = $sync['uid'];
            return $this->where('uid',$uid)->find();
        }else{
            //是否已有开放平台相同的账户
            if (!empty($data['unionid'])){
                $has_union = $syncModel->where('unionid',$data['unionid'])->find();
                if ($has_union) $has_union = $has_union->toArray();
            }
            if (isset($has_union)){
                $uid = $has_union['uid'];
            }else{
                $member_data = [
                    //'uid'       => 'default',
                    'shopid'    => $data['shopid'],
                    'nickname'  => $data['nickname'],
                    'username'  => rand_username(''),
                    'password'  => user_md5('123456', Config::get('auth.auth_key')),
                    'avatar'    => $data['avatar'],
                    'sex'       => $data['sex'],
                    'email'     => $this->randEmail(),
                    'status'    =>  1
                ];
                $result = $this->save($member_data);
                if (!$result){
                    throw new Exception('存入用户信息失败');
                }
                //将用户添加到用户组
                (new AuthGroup())->addToGroup($this->id ,1);
                $uid = $this->id;
            }


            $sync = $syncModel->edit([
                'uid'       => $uid,
                'openid'    => $data['openid'],
                'unionid'   => $data['unionid'],
                'type'      => $data['oauth_type']
            ]);
            //存入授权记录
            if(!$sync){
                throw new Exception('存入用户授权记录失败');
            }
            $actionLog = new ActionLog();
            $actionLog->add('reg','member',1,$uid);
        }
        return $this->where('uid',$uid)->find();
    }

    /**
     * 更新用户余额 或积分
     * @param $uid
     * @param $field
     * @param $num
     * @param int $type
     * @return Member|bool
     */
    public static function updateAmount($uid,$field,$num,$type = 1){
        $value = Member::where('uid',$uid)->value($field);
        //加法
        if ($type == 1){
            $value = bcadd($value,$num,2);
        }else{
            $value = bcsub($value,$num,2);
        }
        $result = Member::where('uid',$uid)->update([
            $field => $value
        ]);
        if ($result !== false){
            $result = true;
        }
        return $result;
    }

    public function edit($data){
        if ($data['uid']){
            $result = $this->where('uid',$data['uid'])->update($data);
            if ($result !== false){
                return true;
            }
        }
        return false;
    }

    /**
     * @title 生成一个用户
     * @param string $username
     * @param string $nickname
     * @param string $password
     * @param string $mobile
     * @param string $email
     * @return bool|int
     */
    public function randMember($username = '',$nickname = '',$password = '',$email = '',$mobile = ''){
        //昵称注册开关
        if (config('system.USER_NICKNAME_SWITCH') == 0 || empty($nickname)) {
            $nickname = rand_nickname(config('system.USER_NICKNAME_PREFIX'));
        }
        //用户名称
        $username = $username ?: rand_username('用户');
        $password = $password ?: 123456;
        $email = $email ?: $this->randEmail();
        return $this->register($username,$nickname,$password,$email,$mobile);
    }


}
