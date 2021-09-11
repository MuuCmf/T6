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
        if(is_numeric($search)) {
            //UID查询
            $map['uid'] = $search;
        }else{
            $username = $search;
            $aUnType = 0;
            check_username($username, $email, $mobile, $aUnType);
            //用户名或昵称查询
            if($username){
                $mapUsername['username'] = ['like', '%' . $username . '%'];

                $uid = Db::name('member')->where($mapUsername)->value('id');
                if($uid){
                    $map['uid'] = $uid;
                }else{
                    $map['nickname'] = ['like', '%' . (string)$search . '%'];
                }
            }
        }
        //排序
        $sort = input('order','','text');
        $order='';
        if($sort == 'uid'){
            $order = 'uid desc';
        }
        if($sort == 'reg_time'){
            $order = 'reg_time desc';
        }
        if($sort == 'login_time'){
            $order = 'last_login_time desc';
        }
        if($sort == 'login_num'){
            $order = 'login desc';
        }

        $map[] = ['status','>=', 0];
        list($list,$page) = $this->commonLists('Member', $map, $order);

        $list_arr = $list->toArray()['data'];

        foreach($list_arr as $key=>$v){
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

        $this->setTitle('用户列表');
        View::assign('title','用户列表');
        View::assign('page',$page);
        View::assign('_list', $list_arr);
        
        return View::fetch();
    }

    /**
     * 重置用户密码
     */
    public function initPass()
    {
        $uids = input('param.id/a');
        !is_array($uids) && $uids = explode(',', $uids);
        foreach ($uids as $key => $val) {
            if (!query_user(['uid'], $val)) {
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

    /**用户资料详情修改
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
            // 更新积分数据
            $this->memberModel->where(['uid' => $uid])->update($data_score);
            
            foreach ($data_score as $key => $val) {
                $value = query_user($uid,array($key));
                if ($val == $value[$key]) {
                    continue;
                }
                $scoreLogModel = new ScoreLogModel();
                //写积分变化日志
                $scoreLogModel->addScoreLog($uid, cut_str('score', $key, 'l'), 'to', $val, '', 0, get_nickname(is_login()) . '后台调整');
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
            // clean_query_user_cache($uid);
            //if ($rs_member) {
                return $this->success('保存成功');
            //} else {
                //$this->error('保存失败');
            //}
        } else {
            // 获取用户数据
            $memberModel = new MemberModel();
            $uid = input('uid');
            $map[] = ['uid', '=', $uid];
            $map[] = ['status', '>=', 0];
            $member = $memberModel->where($map)->find()->toArray();
            //扩展信息查询
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

            $auth = Db::name('auth_group_access')->where(['uid'=>$uid])->select();
            $auth_group = [];
            foreach($auth as $key=>$val){
                $auth_group[] = $val['group_id'];
            }
            $member['auth_group'] = implode(',',$auth_group);
            /**/

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

            $scoreTypeModel = new ScoreTypeModel();
            /* 积分设置 */
            $field = $scoreTypeModel->getTypeList([['status','=', 1]]);
            $score_key = [];
            foreach ($field as $vf) {
                $score_key[] = 'score' . $vf['id'];
                $builder->keyText('score' . $vf['id'], $vf['title']);
            }

            $score_data = $memberModel->where('uid', '=', $uid)->field(implode(',', $score_key))->find()->toArray();
            $member = array_merge($member, $score_data);
            /*积分设置end*/

            /*权限组*/
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
     * 会员状态修改
     */
    public function status($method = null)
    {
        $id = array_unique((array)input('id/a', 0));
        if (count(array_intersect(explode(',', config('auth.auth_administrator')), $id)) > 0) {
            $this->error('不允许对超管进行该操作');
        }
        $id = is_array($id) ? implode(',', $id) : $id;
        if (empty($id)) {
            $this->error('请选择要操作的数据');
        }

        $map[] = ['uid', 'in', $id];

        switch (strtolower($method)) {
            case 'forbiduser':
                return $this->forbid('Member', $map);
                break;
            case 'resumeuser':
                return $this->resume('Member', $map);
                break;
            case 'deleteuser':
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

}
