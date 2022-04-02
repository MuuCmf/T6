<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Api.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/4
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\ucenter\controller;
use app\common\controller\Api as ApiBase;
use app\common\model\Feedback;
use app\common\model\Member;
use app\common\model\MemberWallet;
use app\common\model\Verify;
use app\channel\service\wechat\MiniProgram;
use think\Request;

class Api extends ApiBase{
    protected $MemberModel;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    function __construct()
    {
        parent::__construct();
        $this->MemberModel = new Member();
    }

    /**
     * @title 获取用户信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserInfo(){
        $uid = request()->uid;
        //查询用户信息
        $user = query_user($uid,['uid','nickname','avatar','email','mobile','realname','sex','qq','score1','birthday','signature']);
        if ($user){
            //格式化生日
            $birthday = strtotime($user['birthday']);
            $birthday = $birthday > 0 ? $birthday : time();
            $user['birthday'] = date('Y年m月d日',$birthday);
            //获取钱包数据
            $wallet = (new MemberWallet())->where('uid',$uid)->field('balance,freeze,revenue')->find();
            if ($wallet){
                $user['wallet'] = $wallet->toArray();
            }else{
                $user['wallet'] = [
                    'balance'   =>  0,
                    'freeze'    =>  0,
                    'revenue'   =>  0
                ];
            }
            $this->success('success',$user);
        }
        $this->error('没有查询到用户数据');
    }

    /**
     *@title 修改用户信息
     */
    public function edit(){
        if (\request()->post()){
            $birthday_format = date_parse_from_format('Y年m月d日',$this->params['birthday']);
            $birthday = mktime(0,0,0,$birthday_format['month'],$birthday_format['day'],$birthday_format['year']);
            $birthday = date('Y-m-d',$birthday);
            $data = [
                'uid'   =>  \request()->uid,
                'nickname'  =>  $this->params['nickname'],
                'sex'       =>  $this->params['sex'],
                'birthday'  =>  $birthday,
                'signature' =>  $this->params['signature']
            ];
            $result = $this->MemberModel->edit($data);
            if ($result){
                $this->success('修改成功');
            }
            $this->error('网络异常，请稍后再试');
        }
    }

    /**
     * 更换用户头像
     */
    public function avatar(){
        $uid = request()->uid;
        $avatar = input('post.avatar');
        $res = $this->MemberModel->edit([
            'uid' => $uid,
            'avatar' => $avatar
        ]);
        if ($res !== false){
            $this->success('更新成功');
        }
        $this->error('更新失败');
    }

    /**
     * 绑定手机号
     */
    public function mobile(){
        $uid = request()->uid;
        $mobile = input('post.mobile');
        $code = input('post.code');

        if (empty($mobile)){
            $this->error('请输入手机号');
        }
        if (empty($code)){
            $this->error('请输入验证码');
        }
        $verifyModel = new Verify();
        if (!$verifyModel->checkVerify($mobile, 'mobile', $code)) {
            $this->error('验证码错误');
        }

        $has_bind = $this->MemberModel->where('mobile',$mobile)->count();
        if ($has_bind > 0){
            $this->error('当前手机号已被他人绑定');
        }
        $data = ['uid' => $uid,'mobile' => $mobile];
        $res = $this->MemberModel->edit($data);
        if ($res){
            $this->success('绑定成功');
        }
        $this->error('绑定失败');
    }

    /**
     * 绑定邮件
     */
    public function email(){
        $uid = request()->uid;
        $email = input('post.email');
        $code = input('post.code');

        if (empty($email)){
            $this->error('请输入邮箱');
        }
        if (empty($code)){
            $this->error('请输入验证码');
        }
        $verifyModel = new Verify();
        if (!$verifyModel->checkVerify($email, 'email', $code)) {
            $this->error('验证码错误');
        }

        $has_bind = $this->MemberModel->where('email',$email)->count();
        if ($has_bind > 0){
            $this->error('当前邮箱已被他人绑定');
        }
        $data = ['uid' => $uid,'email' => $email];
        $res = $this->MemberModel->edit($data);
        if ($res){
            $this->success('绑定成功');
        }
        $this->error('绑定失败');
    }

    /**
     * 建议、反馈
     */
    public function feedback(){
        $uid = request()->uid;
        $content = input('post.content','');
        if (empty($content)){
            $this->error('内容不能为空');
        }
        if (input('?post.images')){
            $images = input('post.images');
            $images = explode(',', $images);
        }else{
            $images = '';
        }
        $data = [
            'shopid' => $this->shopid,
            'app' => get_module_name(),
            'content' => $content,
            'images' => $images,
            'uid' => $uid,
        ];
        $res = (new Feedback())->edit($data);
        if ($res){
            $this->success('提交成功，我们会尽快处理您的反馈');
        }
        $this->error('网络异常，请稍后再试');
    }

}