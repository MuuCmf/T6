<?php
declare (strict_types = 1);

namespace app\ucenter\controller;

use app\common\model\MemberSync;
use think\facade\Db;
use think\facade\View;
use app\common\controller\Common;
use app\common\model\Attachment;
use app\common\model\Verify;
use app\common\model\Member;
use app\common\model\ScoreType;
use app\common\model\Action;

class Config extends Common
{
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    /**
     * 用户中心
     */
    public function index()
    {
        if (Request()->isPost()) {
            $aNickname = input('post.nickname', '', 'text');
            $aSex = input('post.sex', 0, 'intval');
            $aSignature = input('post.signature', '', 'text');
            // $birthday = input('post.birthday', 0, 'intval');
            // $birthday_format = date_parse_from_format('Y年m月d日', $birthday);
            // $birthday = mktime(0,0,0,$birthday_format['month'], $birthday_format['day'], $birthday_format['year']);
            // $birthday = date('Y-m-d',$birthday);
            
            $uid = intval(get_uid());
            $commonMemberModel = new Member();
            $check = $commonMemberModel->checkNickname($aNickname, $uid);
            if($check !== true){
                return $this->error($check);
            }
            $user['nickname'] = $aNickname;
            $user['sex'] = $aSex;
            $user['signature'] = $aSignature;
            //$user['birthday']  =  $birthday;
            $res = Db::name('Member')->where('uid', $uid)->update($user);
            
            if ($res) {
                return $this->success('设置成功');

            } else {
                return $this->error('设置失败');
            }

        } else {
            //调用基本信息
            $user = query_user(is_login(),['username','nickname', 'signature', 'email', 'mobile', 'avatar', 'sex', 'birthday']);
            //显示页面
            View::assign('user', $user);
            // $this->_getExpandInfo();
            // 当前方法赋值变量
            View::assign('tab', 'index');

            return View::fetch();
        }
    }

    /**
     * @title 获取用户信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userInfo()
    {
        $uid = get_uid();
        //查询用户信息
        $user = query_user($uid,['uid','nickname','avatar','email','mobile','realname','sex','qq','score1','birthday','signature']);
        if ($user){
            //格式化生日
            $birthday = strtotime($user['birthday']);
            $birthday = $birthday > 0 ? $birthday : time();
            $user['birthday'] = date('Y年m月d日',$birthday);
            
            return $this->success('success',$user);
        }
        $this->error('没有查询到用户数据');
    }

    /**
     * 绑定用户手机或邮箱
     */
    public function account()
    {
        if(request()->isPost()){
            $account = input('account', '', 'text');
            $type = input('type', '', 'text');
            $verify = input('verify', '', 'text');
            $type = $type == 'mobile' ? 'mobile' : 'email';
            $type_str = $type == 'mobile' ? '手机号' : 'email';

            // 验证手机号码唯一性
            $has_map = [
                ['shopid', '=', $this->shopid],
            ];
            if($type == 'mobile'){
                $has_map[] = ['mobile', '=', $account];
            }
            if($type == 'email'){
                $has_map[] = ['email', '=', $account];
            }
            $commonMemberModel = new Member();
            $has_account = $commonMemberModel->where($has_map)->find();

            if($has_account){
                return $this->error($type_str . '已绑定其他用户');
            }

            // 验证验证码
            if (($type == 'mobile') || $type == 'email') {
                $verifyModel = new Verify();
                if (!$verifyModel->checkVerify($account, $type, $verify)) {
                    return $this->error('验证码错误');
                }
            }
            if($type == 'mobile'){
                $data = [
                    'mobile' => $account,
                ];
            }
            if($type == 'email'){
                $data = [
                    'email' => $account,
                ];
            }
            
            $res = Db::name('Member')->where(['uid' => get_uid()])->update($data);
            if ($res) {
                return $this->success('保存成功');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $aTag = input('tag', '', 'text');
            $aTag = $aTag == 'mobile' ? 'mobile' : 'email';
            View::assign('cName', $aTag == 'mobile' ? '手机号' : '邮箱');
            View::assign('type', $aTag);

            return View::fetch();
        }
    }

    /**
     *@title 修改用户信息
     */
    public function edit()
    {
        if (\request()->post()){
            $birthday_format = date_parse_from_format('Y年m月d日',$this->params['birthday']);
            $birthday = mktime(0,0,0,$birthday_format['month'],$birthday_format['day'],$birthday_format['year']);
            $birthday = date('Y-m-d',$birthday);
            $data = [
                'uid'   =>  get_uid(),
                'nickname'  =>  $this->params['nickname'],
                'sex'       =>  $this->params['sex'],
                'birthday'  =>  $birthday,
                'signature' =>  $this->params['signature']
            ];
            $result = (new Member)->edit($data);
            if ($result){
                return $this->success('修改成功');
            }
            return $this->error('网络异常，请稍后再试');
        }
    }

        /**
     * 绑定手机号
     */
    public function mobile()
    {
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

        $memberModel = new Member;
        $has_bind = $memberModel->where('mobile',$mobile)->count();
        if ($has_bind > 0){
            $this->error('当前手机号已被他人绑定');
        }
        $data = ['uid' => $uid,'mobile' => $mobile];
        $res = $memberModel->edit($data);
        if ($res){
            return $this->success('绑定成功');
        }
        return $this->error('绑定失败');
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
        $memberModel = new Member;

        $has_bind = $memberModel->where('email',$email)->count();
        if ($has_bind > 0){
            $this->error('当前邮箱已被他人绑定');
        }
        $data = ['uid' => $uid,'email' => $email];
        $res = $memberModel->edit($data);
        if ($res){
            return $this->success('绑定成功');
        }
        return $this->error('绑定失败');
    }

    /**
     * 修改密码
     * @return [type] [description]
     */
    public function password()
    {
        if(request()->isPost()){
            $old_password = input('post.old_password','','text');
            $new_password = input('post.new_password','','text');
            $confirm_password = input('post.confirm_password','','text');
            //调用接口
            $commonMemberModel = new Member;
            $resCode = $commonMemberModel->changePassword($old_password, $new_password, $confirm_password);

            if ($resCode>0) {
                return $this->success('密码修改成功');
            } else {
                return $this->error($commonMemberModel->error);
            }
        }else{
            //调用基本信息
            $user = query_user(is_login(),['nickname', 'signature', 'email', 'mobile', 'avatar', 'sex']);
            //显示页面
            View::assign('user', $user);
            View::assign('tab', 'password');
            return View::fetch(); 
        }
    }

    /**
     * saveAvatar  保存头像
     */
    public function avatar()
    {
        if (request()->isPost()) {
            $crop = input('post.crop', '', 'text');
            $uid = is_login();
            $path = input('post.path', '', 'text');
            $avatar = input('post.avatar', '', 'text');
            
            $memberModel = new Member();
            if(!empty($avatar)){
                $res = $memberModel->edit([
                    'uid' => $uid,
                    'avatar' => $avatar
                ]);
            }else{
                if (empty($crop)) {
                    return $this->error('参数错误');
                }
    
                // 裁切图片
                $Attachment = new Attachment();
                $path = $Attachment->cropImage($path, $crop);
    
                //更新数据库数据
                $data = [
                    'avatar' => $path,
                ];
                $res = Db::name('Member')->where(['uid' => $uid])->update($data);
            }

            if ($res) {
                return $this->success('保存成功');
            }else{
                return $this->error('保存失败');
            }
        }else{
            //dump(config());
            // 基本信息
            $user = query_user(is_login(),['nickname', 'avatar']);
            
            //显示页面
            View::assign('user', $user);
            View::assign('tab', 'avatar');
            return View::fetch();
        }
        
    }

    /**
     * 我的积分
     * @return [type] [description]
     */
    public function score()
    {
        $scoreModel = new ScoreType();

        $scores = $scoreModel->getTypeList(['status'=>1]);
        foreach ($scores as &$v) {
            $v['value'] = $scoreModel->getUserScore(is_login(), $v['id']);
        }

        unset($v);
        View::assign('scores', $scores);

        $level = config('system.USER_LEVEL');
        View::assign('level', $level);

        $self = query_user(get_uid(), array('nickname','avatar' ,'score1', 'score2', 'score3', 'score4'));

        View::assign('user', $self);

        $actionModel = new Action();
        $action = $actionModel->getAction(['status' => 1]);
        $action_module = [];
        
        foreach ($action as &$v) {
            $v['rule_array'] = unserialize($v['rule']);
            if(is_array($v['rule_array'])){
                foreach ($v['rule_array'] as &$o) {
                    if (is_numeric($o['rule'])) {
                        $o['rule'] = $o['rule'] > 0 ? '+' . intval($o['rule']) : $o['rule'];
                    }
                    $o['score'] = $scoreModel->getType(['id' => $o['field']]);
                }
            }
            if ($v['rule_array'] != false) {
                $action_module[$v['module']]['action'][] = $v;
            }
        }
        unset($v);

        // foreach ($action_module as $key => &$a) {
        //     if (empty($a['action'])) {
        //         unset($action_module[$key]);
        //     }
        //     $a['module'] = model('common/Module')->getModule($key);
        // }
        // unset($a);
        View::assign('action_module', $action_module);
        
        View::assign('tab', 'score');
        return View::fetch();
    }

    //积分规则
    public function scorerule()
    {
        $scoreModel = new ScoreType();

        $scores = $scoreModel->getTypeList(['status'=>1]);
        foreach ($scores as &$v) {
            $v['value'] = $scoreModel->getUserScore(is_login(), $v['id']);
        }
        unset($v);
        View::assign('scores', $scores);

        $level = config('system.USER_LEVEL');
        View::assign('level', $level);

        $self = query_user(get_uid(), array('nickname','avatar' ,'score1', 'score2', 'score3', 'score4'));

        View::assign('user', $self);

        $actionModel = new Action();
        $action = $actionModel->getAction(['status' => 1]);
        $action_module = [];
        
        foreach ($action as &$v) {
            $v['rule_array'] = unserialize($v['rule']);
            if(is_array($v['rule_array'])){
                foreach ($v['rule_array'] as &$o) {
                    if (is_numeric($o['rule'])) {
                        $o['rule'] = $o['rule'] > 0 ? '+' . intval($o['rule']) : $o['rule'];
                    }
                    $o['score'] = $scoreModel->getType(['id' => $o['field']]);
                }
            }
            if ($v['rule_array'] != false) {
                $action_module[$v['module']]['action'][] = $v;
            }
        }
        unset($v);

        // foreach ($action_module as $key => &$a) {
        //     if (empty($a['action'])) {
        //         unset($action_module[$key]);
        //     }
        //     $a['module'] = model('common/Module')->getModule($key);
        // }
        // unset($a);
        View::assign('action_module', $action_module);
        
        View::assign('tab', 'scorerule');
        return View::fetch();
    }

    /**
     * 积分等级
     */
    public function score_estate()
    {
        $scoreModel = new ScoreType();

        $scores = $scoreModel->getTypeList(['status'=>1]);
        foreach ($scores as &$v) {
            $v['value'] = $scoreModel->getUserScore(is_login(), $v['id']);
        }
        unset($v);
        View::assign('scores', $scores);

        $level = config('system.USER_LEVEL');
        View::assign('level', $level);

        $self = query_user(get_uid(), array('nickname','avatar' ,'score1', 'score2', 'score3', 'score4'));

        View::assign('user', $self);

        $actionModel = new Action();
        $action = $actionModel->getAction(['status' => 1]);
        $action_module = [];
        
        foreach ($action as &$v) {
            $v['rule_array'] = unserialize($v['rule']);
            if(is_array($v['rule_array'])){
                foreach ($v['rule_array'] as &$o) {
                    if (is_numeric($o['rule'])) {
                        $o['rule'] = $o['rule'] > 0 ? '+' . intval($o['rule']) : $o['rule'];
                    }
                    $o['score'] = $scoreModel->getType(['id' => $o['field']]);
                }
            }
            if ($v['rule_array'] != false) {
                $action_module[$v['module']]['action'][] = $v;
            }
        }
        unset($v);

        // foreach ($action_module as $key => &$a) {
        //     if (empty($a['action'])) {
        //         unset($action_module[$key]);
        //     }
        //     $a['module'] = model('common/Module')->getModule($key);
        // }
        // unset($a);
        View::assign('action_module', $action_module);
        
        View::assign('tab', 'score_estate');
        return View::fetch();
    }

    /**
     * 绑定微信账号
     */
    public function wechat(){
        if (request()->isAjax()){
            //绑定用户信息
            $params = input('param.');
            //是否绑定过其他账号
            $bind_map =[];
            $bind_map[] = ['openid','=', $params['openid']];
            $bind_map[] = ['type','=','weixin_h5'];
            // 查询是否已绑定
            $has_bind = boolval((new MemberSync())->where($bind_map)->count());
            if ($has_bind) return $this->error('当前微信已绑定了其他账号');
            $data = [
                'uid'     => get_uid(),
                'openid'  => $params['openid'],
                'unionid' => $params['unionid'] ?? '',
                'type'    => 'weixin_h5'
            ];
            $res = (new MemberSync())->edit($data);
            if ($res){
                return $this->success('绑定成功');
            }
            return $this->error('绑定失败，请稍后再试！');
        }

        $uid = get_uid();
        $self = query_user($uid, ['mobile','nickname','avatar' ,'score1', 'score2', 'score3', 'score4']);
        View::assign('user', $self);
        //是否绑定微信
        $bind_map =[];
        $bind_map[] = ['uid'    ,'=',  $uid];
        $bind_map[] = ['type'   ,'=',  'weixin_h5'];
        $has_bind = (new MemberSync())->where($bind_map)->find();

        View::assign('has_bind', $has_bind);
        View::assign('tab', 'wechat');

        return View::fetch();
    }

    /**
     * 解除微信用户绑定
     */
    public function unbind()
    {
        $map[] = ['uid', '=', get_uid()];
        $map[] = ['type'   ,'=',  'weixin_h5'];
        $res = (new MemberSync())->where($map)->delete();

        if ($res){
            return $this->success('解除绑定成功');
        }
        return $this->error('解除绑定失败，请稍后再试！');
    }

}