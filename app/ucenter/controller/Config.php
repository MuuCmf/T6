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
use app\common\model\Message as MessageModel;
use app\common\model\MessageType as MessageTypeModel;

class Config extends Common
{
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    /**
     * 用户中心首页
     */
    public function index()
    {
        $aNickname = input('post.nickname', '', 'text');
        $aSex = input('post.sex', 0, 'intval');
        $aSignature = input('post.signature', '', 'text');

        if (Request()->isPost()) {
            $uid = is_login();
            $commonMemberModel = new Member;
            $check = $commonMemberModel->checkNickname($aNickname, $uid);
            if($check !== true){
                return $this->error($commonMemberModel->getError());
            }
            $user['nickname'] = $aNickname;
            $user['sex'] = $aSex;
            $user['signature'] = $aSignature;

            $res = Db::name('Member')->where(['uid'=>get_uid()])->update($user);
            if ($res) {
                return $this->success('设置成功');

            } else {
                return $this->error('设置失败');
            }

        } else {
            //调用基本信息
            $user = query_user(is_login(),['username','nickname', 'signature', 'email', 'mobile', 'avatar', 'sex']);
            //显示页面
            View::assign('user', $user);
            // $this->_getExpandInfo();
            // 当前方法赋值变量
            View::assign('tab', 'index');

            return View::fetch();
        }
    }

    /**
     * changeaccount  修改帐号信息
     */
    public function account()
    {
        if(request()->isPost()){
            $account = input('account', '', 'text');
            $type = input('type', '', 'text');
            $verify = input('verify', '', 'text');
            $type = $type == 'mobile' ? 'mobile' : 'email';

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
            
            $res = Db::name('Member')->where(['uid' => is_login()])->update($data);
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
        if (Request()->isPost()) {
            $crop = input('post.crop', '', 'text');
            $uid = is_login();
            $path = input('post.path', '', 'text');
            
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
     * 消息模态框
     */
    public function message()
    {
        // 获取消息类型
        $MessageModel = new MessageModel();
        $MessageTypeModel = new MessageTypeModel();
        // 查询条件
        $map[] = ['shopid', '=', 0];
        $map[] = ['status', '=', 1];
        $type_list = $MessageTypeModel->getList($map);
        foreach($type_list as &$val){
            $val = $MessageTypeModel->formatData($val);
        }
        unset($val);
        View::assign('type_list', $type_list);
        
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