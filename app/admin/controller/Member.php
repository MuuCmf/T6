<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminSortBuilder;

use app\common\model\Member as MemberModel;
use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;
use app\common\model\ScoreLog as ScoreLogModel;
use app\common\model\ScoreType as ScoreTypeModel;

/**
 * 后台用户控制器
 */
class Member extends Admin
{
    protected $memberModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->memberModel = new MemberModel();
    }

    /**
     * 用户管理首页
     */
    public function index()
    {
        $search = input('search','','text');
        if(!empty($search)) {
            $uids = $this->memberModel
            ->where('username', 'like', '%' . $search . '%')
            ->whereOr('nickname', 'like', '%' . $search . '%')
            ->whereOr('mobile', 'like', '%' . $search . '%')
            ->whereOr('email', 'like', '%' . $search . '%')
            ->column('uid');
            if(!empty($uids)){
                $map[] = ['uid' ,'in' ,$uids];
            }else{
                $map[] = ['nickname', 'like', '%' . $search . '%'];
            }
        }

        //排序
        $sort = input('order','create_time','text');
        $order='';
        if($sort == 'uid'){
            $order = 'uid desc';
        }
        if($sort == 'create_time'){
            $order = 'create_time desc';
        }
        if($sort == 'last_login_time'){
            $order = 'last_login_time desc';
        }
        if($sort == 'login'){
            $order = 'login desc';
        }
        $map[] = ['status','>=', 0];
        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        $list = $this->memberModel->where($map)->order($order)->paginate($rows);
        $pager = $list->render();
        $list = $list->toArray();
        $list_arr = $list['data'];

        foreach($list_arr as $key=>$v){
            //处理用户头像
            if(empty($list_arr[$key]['avatar'])){
                $list_arr[$key]['avatar'] = $list_arr[$key]['avatar64'] = $list_arr[$key]['avatar128'] = $list_arr[$key]['avatar256'] = $list_arr[$key]['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
            }else{
                $list_arr[$key]['avatar64'] = get_thumb_image($list_arr[$key]['avatar'], 64, 64);
                $list_arr[$key]['avatar128'] = get_thumb_image($list_arr[$key]['avatar'], 128, 128);
                $list_arr[$key]['avatar256'] = get_thumb_image($list_arr[$key]['avatar'], 256, 256);
                $list_arr[$key]['avatar512'] = get_thumb_image($list_arr[$key]['avatar'], 512, 512);
            }
            //获取权限组
            $auth_g_id = Db::name('auth_group_access')->where(['uid'=>$v['uid']])->select()->toArray();
            foreach($auth_g_id as $k=>$val){
                $auth_group = Db::name('auth_group')->where(['id'=>$val['group_id']])->value('title');
                $list_arr[$key]['auth_group'][$k]['title'] = $auth_group;
            }
            unset($k);
            unset($val);
        }

        int_to_string($list_arr);
        if (request()->isAjax()){
            $list['data'] = $list_arr;
            return $this->success('success',$list);
        }
        $this->setTitle('用户列表');
        View::assign('title','用户列表');
        View::assign('pager',$pager);
        View::assign('_list', $list_arr);
        
        return View::fetch();
    }

    /**
     * 重置用户密码
     */
    public function initPass()
    {
        $uids = input('id/a');
        !is_array($uids) && $uids = explode(',', $uids);

        foreach ($uids as $key => $val) {
            if (!query_user($val, ['uid'])) {
                unset($uids[$key]);
            }
        }
        if (!count($uids)) {
            return $this->error('重置失败');
        }
        $data['password'] = user_md5('123456',config('auth.auth_key'));
        $res = $this->memberModel->where('uid','in', $uids)->update(['password' => $data['password']]);
        if ($res) {
            return $this->success('重置密码成功');
        } else {
            return $this->error('重置用户密码失败');
        }
    }

    /**
     * 用户资料详情修改
     * @param string $uid
     * @author 大蒙<59262424@qq.com>
     */
    public function edit()
    {   
        if (request()->isPost()) {
            $data = input('post.');
            $uid = $data['uid'];
            $data_score = [];
            /* 修改积分 start*/
            foreach ($data as $key => $val) {
                if (substr($key, 0, 5) == 'score') {
                    $data_score[$key] = $val;
                }
            }
            foreach ($data_score as $key => $val) {
                $user_score = query_user($uid,array($key));
                // 值相同跳过
                if (intval($val) == intval($user_score[$key])) {
                    continue;
                }else{
                    // 更新积分数据
                    $this->memberModel->where(['uid' => $uid])->update($data_score);
                    //写积分变化日志
                    if (intval($val) > intval($user_score[$key])) {
                        $action = 'inc';
                        $value = intval($val) - intval($user_score[$key]);
                    }else{
                        $action = 'dec';
                        $value = intval($user_score[$key]) - intval($val);
                    }
                    $scoreLogModel = new ScoreLogModel();
                    $scoreLogModel->addScoreLog($uid, cut_str('score', $key, 'l'), $action, $value, '', 0, get_nickname(is_login()) . '后台调整');
                }
            }
            /* 修改积分 end*/

            /*用户组设置*/
            //如果设置了默认积分会新增积分
            $authGroup = new AuthGroup();
            $authGroup->addToGroup($uid, $data['auth_group']);
            /*用户组END*/

            //基础设置
            $map['uid'] = $uid;
            $aNickname = $data['nickname'];
            $this->checkNickname($aNickname, $uid);

            //用户名、邮箱、手机变成可编辑内容
            $avatar = $data['avatar'];
            $aUsername = $data['username'];
            $aEmail = $data['email'];
            $aMobile = $data['mobile'];
            if($aUsername == '' && $aEmail == '' && $aMobile == ''){
                return $this->error('用户名、邮箱、手机号，至少填写一项！');
            }
            if($aEmail!=''){
                if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $aEmail)) {
                    return $this->error('请正确填写邮箱！');
                }
            }
            if($aMobile!=''){
                if (!preg_match("/^\d{11}$/", $aMobile)) {
                    return $this->error('请正确填写手机号！');
                }
            }
            $aRealname = $data['realname'];
            $aSex = $data['sex'];
            $memberData = [
                'uid' => $uid,
                'avatar' => $avatar,
                'username' => $aUsername,
                'email' => $aEmail,
                'mobile' => $aMobile,
                'nickname' => $aNickname,
                'realname' => $aRealname,
                'sex' => $aSex,
            ];
            $rs_member = $this->memberModel->where(['uid' => $uid])->update($memberData);
            // 用户名、邮箱、手机变成可编辑内容end

            // 清理用户缓存
            // 
            return $this->success('保存成功');
            
        } else {
            // 获取用户数据
            $uid = input('uid');
            $member = $this->memberModel->where('uid','=',$uid)->find()->toArray();
            
            // 扩展资料
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
                } else {
                    $member[$key] = $field_data;
                }
                $member[$key] = $field_data;
            }
            $builder = new AdminConfigBuilder();
            $builder->title('用户扩展资料详情');
            $builder->keyUid()
                    ->keySingleImage('avatar','头像','')
                    ->keyText('email','邮箱')
                    ->keyText('mobile','手机号')
                    ->keyText('username', '用户名')
                    ->keyText('nickname', '昵称')
                    ->keyText('realname', '真实姓名')
                    ->keyRadio('sex','性别','',[0 => '不详',1 => '男', 2 => '女']);

            $field_key = ['uid','avatar', 'username','email','mobile', 'nickname', 'realname', 'sex'];
            foreach ($fields_list as $vt) {
                $field_key[] = $vt['field_name'];
            }

            /* 积分设置 */
            $scoreTypeModel = new ScoreTypeModel();
            $field = $scoreTypeModel->getTypeList([['status','=', 1]]);
            $score_key = [];
            foreach ($field as $vf) {
                $score_key[] = 'score' . $vf['id'];
                $builder->keyText('score' . $vf['id'], $vf['title']);
            }
            // $score_data = $this->memberModel->where('uid', '=', $uid)->field(implode(',', $score_key))->find()->toArray();
            // $member = array_merge($member, $score_data);
            /*积分设置end*/

            /*权限组*/
            // 用户拥有的权限组
            $auth = Db::name('auth_group_access')->where(['uid'=>$uid])->select();
            $auth_group = [];
            foreach($auth as $key=>$val){
                $auth_group[] = $val['group_id'];
            }
            $member['auth_group'] = implode(',',$auth_group);
            // 系统设置启用的权限组
            $auth_group = Db::name('auth_group')->where('status', '=', 1)->select()->toArray();
            $auth_group_options = [];
            foreach ($auth_group as $val) {
               $auth_group_options[$val['id']] = $val['title'];
            }
            $builder->keyCheckBox('auth_group', '权限组', '可以多选', $auth_group_options);

            /*权限组end*/
            $builder->data($member);
            $builder
                ->group('基础设置', implode(',', $field_key))
                ->group('积分设置', implode(',', $score_key))
                ->group('权限组', 'auth_group')
                ->buttonSubmit('', '保存')
                ->buttonBack()
                ->display();
        }
    }

    /**
     * 用户详情
     */
    public function detail()
    {
        $uid = input('uid',0, 'intval');
        $map[] = ['uid', '=', $uid];
        $member = $this->memberModel->where($map)->find()->toArray();

        View::assign('member', $member);

        return View::fetch();
    }

    /**
     * 会员状态修改
     */
    public function status($method = null)
    {
        $ids = input('ids');
        !is_array($ids)&&$ids=explode(',',$ids);
        if (count(array_intersect(explode(',', config('auth.auth_administrator')), $ids)) > 0) {
            $this->error('不允许对超管进行该操作');
        }
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map[] = ['uid', 'in', $ids];

        switch (strtolower($method)) {
            case 'forbid':
                return $this->forbid('Member', $map);
                break;
            case 'resume':
                return $this->resume('Member', $map);
                break;
            case 'delete':
                return $this->delete('Member', $map);
                break;
            default:
                return $this->error('参数错误');

        }
    }

    public function getNickname()
    {
        $uid = input('get.uid', 0, 'intval');
        if ($uid) {
            $user = query_user($uid);

            return json($user);

        } else {

            return null;
        }

    }

    /**
     * 验证用户名
     * @param $nickname
     */
    private function checkNickname($nickname, $uid)
    {
        $length = mb_strlen($nickname, 'utf8');
        if ($length == 0) {
            return $this->error('请输入昵称');
        } else if ($length > config('system.NICKNAME_MAX_LENGTH',32)) {
            return $this->error('昵称不能超过'. config('system.NICKNAME_MAX_LENGTH',32).'个字');
        } else if ($length < config('system.NICKNAME_MIN_LENGTH',2)) {
            return $this->error('昵称不能少于' . config('system.NICKNAME_MIN_LENGTH',2) . '个字');
        }
        $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
        if (!$match) {
            return $this->error('昵称只允许中文、字母、下划线和数字');
        }
        //验证唯一性
        $map_nickname[] = ['nickname', '=', $nickname];
        $map_nickname[] = ['uid','<>', $uid];
        $had_nickname = Db::name('Member')->where($map_nickname)->count();

        if ($had_nickname) {
            return $this->error('昵称已被人使用');
        }
        $denyName = Db::name("config")->where(['name' => 'USER_NAME_BAOLIU'])->value('value');
        if ($denyName != '') {
            $denyName = explode(',', $denyName);
            foreach ($denyName as $val) {
                if (!is_bool(strpos($nickname, $val))) {
                    return $this->error('该昵称已被禁用');
                }
            }
        }
    }


    /**
     * Modal 选择用户信息
     * @return \think\response\View
     * @throws \think\db\exception\DbException
     */
    function chooseUser(){
        $search = input('search','','text');
        $oauth_type = input('oauth_type','','text');//授权条件
        if(is_numeric($search)) {
            //UID查询
            $map[] = ['m.uid','=',$search];
        }else{
            $nickname = $search;
            //用户名或昵称查询
            if($nickname){
                $mapNickname = ['nickname' ,'like', '%' . $nickname . '%'];
                $uid = Db::name('member')->where($mapNickname)->value('id');
                if($uid){
                    $map[] = ['m.uid','=',$search];
                }else{
                    $map[] = ['m.nickname' ,'like', '%' . (string)$search . '%'];
                }
            }
        }

        $map[] = ['m.status','>=', 0];
        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        if (empty($oauth_type)){
            $list = $this->memberModel->alias('m')->where($map)->order('uid','desc')->paginate($rows);
        }else{
            $map[] = ['ms.type','=',$oauth_type];
            $list = $this->memberModel->alias('m')->join('member_sync ms','m.uid = ms.uid')->where($map)->order('m.uid','desc')->paginate($rows);
        }

        $pager = $list->render();
        $list = $list->toArray();
        $list_arr = $list['data'];

        foreach($list_arr as $key=>$v){
            //处理用户头像
            if(empty($list_arr[$key]['avatar'])){
                $list_arr[$key]['avatar'] = $list_arr[$key]['avatar64'] = $list_arr[$key]['avatar128'] = $list_arr[$key]['avatar256'] = $list_arr[$key]['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
            }else{
                $list_arr[$key]['avatar64'] = get_thumb_image($list_arr[$key]['avatar'], 64, 64);
                $list_arr[$key]['avatar128'] = get_thumb_image($list_arr[$key]['avatar'], 128, 128);
                $list_arr[$key]['avatar256'] = get_thumb_image($list_arr[$key]['avatar'], 256, 256);
                $list_arr[$key]['avatar512'] = get_thumb_image($list_arr[$key]['avatar'], 512, 512);
            }
        }
        View::assign([
            'pager' => $pager,
            '_list' => $list_arr,
            'oauth_type' => $oauth_type,
            'search' => $search
        ]);
        return \view('_choose_user');
    }

}
