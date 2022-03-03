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
    public function register($username='', $nickname, $password, $email='', $mobile='', $type='username')
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
                    $this->error = '密码错误';
                    return -2; //密码错误
                }
            }
        }
        $this->error = '用户不存在或被禁用';
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
        if($user){
            // 行为限制
            $actionLimit = new ActionLimit();
            $return = $actionLimit->checkActionLimit('input_password','member',$user['uid'],$user['uid']);

            if($return && !$return['code']){
                return $return['msg'];
            }

            if ($user['uid'] && $user['status']) {
                /* 验证用户验证码 */
                $verifyModel = new Verify();
                if (!$verifyModel->checkVerify($account, $type, $captcha)) {
                    return -2;
                }else{
                    return $user['uid']; //返回用户ID
                }
            }
        }
        return -1; //用户不存在或被禁用
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login(int $uid)
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

        return true;
    }

    /**
     * 退出登录
     */
    public function logout(int $uid)
    {
        session(null);

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
        if ($length == 0) {
            $this->error('请输入昵称');
        } else if ($length > Config::get('system.NICKNAME_MAX_LENGTH',32)) {
            $this->error('昵称不能超过'. Config::get('system.NICKNAME_MAX_LENGTH',32).'个字');
        } else if ($length < Config::get('system.NICKNAME_MIN_LENGTH',2)) {
            $this->error('昵称不能少于' . Config::get('system.NICKNAME_MIN_LENGTH',2) . '个字');
        }
        $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
        if (!$match) {
            $this->error('昵称只允许中文、字母、下划线和数字');
        }
        //验证唯一性
        $map_nickname[] = ['nickname', '=', $nickname];
        $map_nickname[] = ['uid','<>', $uid];
        $had_nickname = $this->where($map_nickname)->count();

        if ($had_nickname) {
            $this->error('昵称已被人使用');
        }
        //保留昵称
        $denyName = Config::get('system.USER_NAME_BAOLIU');
        if ($denyName != '') {
            $denyName = explode(',', $denyName);
            foreach ($denyName as $val) {
                if (!is_bool(strpos($nickname, $val))) {
                    $this->error('该昵称已被禁用');
                }
            }
        }

        return true;
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
                    'uid'       => 'default',
                    'shopid'    => $data['shopid'],
                    'nickname'  => $data['nickname'],
                    'username'  => rand_username('oauth'),
                    'password'  => user_md5( 123456,Config::get('auth.auth_key')),
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
     * @title 生成随机昵称
     * @return string
     */
    public function createRandNickname(){
        $tou = array('快乐','冷静','醉熏','潇洒','糊涂','积极','冷酷','深情','粗暴','温柔','可爱','愉快','义气','认真','威武','帅气','传统','潇洒','漂亮','自然','专一','听话','昏睡','狂野','等待','搞怪','幽默','魁梧','活泼','开心','高兴','超帅','留胡子','坦率','直率','轻松','痴情','完美','精明','无聊','有魅力','丰富','繁荣','饱满','炙热','暴躁','碧蓝','俊逸','英勇','健忘','故意','无心','土豪','朴实','兴奋','幸福','淡定','不安','阔达','孤独','独特','疯狂','时尚','落后','风趣','忧伤','大胆','爱笑','矮小','健康','合适','玩命','沉默','斯文','香蕉','苹果','鲤鱼','鳗鱼','任性','细心','粗心','大意','甜甜','酷酷','健壮','英俊','霸气','阳光','默默','大力','孝顺','忧虑','着急','紧张','善良','凶狠','害怕','重要','危机','欢喜','欣慰','满意','跳跃','诚心','称心','如意','怡然','娇气','无奈','无语','激动','愤怒','美好','感动','激情','激昂','震动','虚拟','超级','寒冷','精明','明理','犹豫','忧郁','寂寞','奋斗','勤奋','现代','过时','稳重','热情','含蓄','开放','无辜','多情','纯真','拉长','热心','从容','体贴','风中','曾经','追寻','儒雅','优雅','开朗','外向','内向','清爽','文艺','长情','平常','单身','伶俐','高大','懦弱','柔弱','爱笑','乐观','耍酷','酷炫','神勇','年轻','唠叨','瘦瘦','无情','包容','顺心','畅快','舒适','靓丽','负责','背后','简单','谦让','彩色','缥缈','欢呼','生动','复杂','慈祥','仁爱','魔幻','虚幻','淡然','受伤','雪白','高高','糟糕','顺利','闪闪','羞涩','缓慢','迅速','优秀','聪明','含糊','俏皮','淡淡','坚强','平淡','欣喜','能干','灵巧','友好','机智','机灵','正直','谨慎','俭朴','殷勤','虚心','辛勤','自觉','无私','无限','踏实','老实','现实','可靠','务实','拼搏','个性','粗犷','活力','成就','勤劳','单纯','落寞','朴素','悲凉','忧心','洁净','清秀','自由','小巧','单薄','贪玩','刻苦','干净','壮观','和谐','文静','调皮','害羞','安详','自信','端庄','坚定','美满','舒心','温暖','专注','勤恳','美丽','腼腆','优美','甜美','甜蜜','整齐','动人','典雅','尊敬','舒服','妩媚','秀丽','喜悦','甜美','彪壮','强健','大方','俊秀','聪慧','迷人','陶醉','悦耳','动听','明亮','结实','魁梧','标致','清脆','敏感','光亮','大气','老迟到','知性','冷傲','呆萌','野性','隐形','笑点低','微笑','笨笨','难过','沉静','火星上','失眠','安静','纯情','要减肥','迷路','烂漫','哭泣','贤惠','苗条','温婉','发嗲','会撒娇','贪玩','执着','眯眯眼','花痴','想人陪','眼睛大','高贵','傲娇','心灵美','爱撒娇','细腻','天真','怕黑','感性','飘逸','怕孤独','忐忑','高挑','傻傻','冷艳','爱听歌','还单身','怕孤单','懵懂');
        $do = array("的","爱","","与","给","扯","和","用","方","打","就","迎","向","踢","笑","闻","有","等于","保卫","演变");
        $wei = array('嚓茶','凉面','便当','毛豆','花生','可乐','灯泡','哈密瓜','野狼','背包','眼神','缘分','雪碧','人生','牛排','蚂蚁','飞鸟','灰狼','斑马','汉堡','悟空','巨人','绿茶','自行车','保温杯','大碗','墨镜','魔镜','煎饼','月饼','月亮','星星','芝麻','啤酒','玫瑰','大叔','小伙','哈密瓜，数据线','太阳','树叶','芹菜','黄蜂','蜜粉','蜜蜂','信封','西装','外套','裙子','大象','猫咪','母鸡','路灯','蓝天','白云','星月','彩虹','微笑','摩托','板栗','高山','大地','大树','电灯胆','砖头','楼房','水池','鸡翅','蜻蜓','红牛','咖啡','机器猫','枕头','大船','诺言','钢笔','刺猬','天空','飞机','大炮','冬天','洋葱','春天','夏天','秋天','冬日','航空','毛衣','豌豆','黑米','玉米','眼睛','老鼠','白羊','帅哥','美女','季节','鲜花','服饰','裙子','白开水','秀发','大山','火车','汽车','歌曲','舞蹈','老师','导师','方盒','大米','麦片','水杯','水壶','手套','鞋子','自行车','鼠标','手机','电脑','书本','奇迹','身影','香烟','夕阳','台灯','宝贝','未来','皮带','钥匙','心锁','故事','花瓣','滑板','画笔','画板','学姐','店员','电源','饼干','宝马','过客','大白','时光','石头','钻石','河马','犀牛','西牛','绿草','抽屉','柜子','往事','寒风','路人','橘子','耳机','鸵鸟','朋友','苗条','铅笔','钢笔','硬币','热狗','大侠','御姐','萝莉','毛巾','期待','盼望','白昼','黑夜','大门','黑裤','钢铁侠','哑铃','板凳','枫叶','荷花','乌龟','仙人掌','衬衫','大神','草丛','早晨','心情','茉莉','流沙','蜗牛','战斗机','冥王星','猎豹','棒球','篮球','乐曲','电话','网络','世界','中心','鱼','鸡','狗','老虎','鸭子','雨','羽毛','翅膀','外套','火','丝袜','书包','钢笔','冷风','八宝粥','烤鸡','大雁','音响','招牌','胡萝卜','冰棍','帽子','菠萝','蛋挞','香水','泥猴桃','吐司','溪流','黄豆','樱桃','小鸽子','小蝴蝶','爆米花','花卷','小鸭子','小海豚','日记本','小熊猫','小懒猪','小懒虫','荔枝','镜子','曲奇','金针菇','小松鼠','小虾米','酒窝','紫菜','金鱼','柚子','果汁','百褶裙','项链','帆布鞋','火龙果','奇异果','煎蛋','唇彩','小土豆','高跟鞋','戒指','雪糕','睫毛','铃铛','手链','香氛','红酒','月光','酸奶','银耳汤','咖啡豆','小蜜蜂','小蚂蚁','蜡烛','棉花糖','向日葵','水蜜桃','小蝴蝶','小刺猬','小丸子','指甲油','康乃馨','糖豆','薯片','口红','超短裙','乌冬面','冰淇淋','棒棒糖','长颈鹿','豆芽','发箍','发卡','发夹','发带','铃铛','小馒头','小笼包','小甜瓜','冬瓜','香菇','小兔子','含羞草','短靴','睫毛膏','小蘑菇','跳跳糖','小白菜','草莓','柠檬','月饼','百合','纸鹤','小天鹅','云朵','芒果','面包','海燕','小猫咪','龙猫','唇膏','鞋垫','羊','黑猫','白猫','万宝路','金毛','山水','音响','尊云','西安');
        $tou_num = rand(0,331);
        $do_num  = rand(0,19);
        $wei_num = rand(0,327);
        return $tou[$tou_num].$do[$do_num].$wei[$wei_num];
    }


}
