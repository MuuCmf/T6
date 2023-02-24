<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\Exception;
use app\admin\builder\AdminConfigBuilder;
use app\common\model\Member as MemberModel;
use app\common\model\MemberSync as MemberSyncModel;
use app\admin\model\AuthGroup;
use app\common\model\ScoreLog as ScoreLogModel;
use app\common\model\ScoreType as ScoreTypeModel;

/**
 * 后台用户控制器
 */
class Member extends Admin
{
    protected $MemberModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->MemberModel = new MemberModel();
    }

    /**
     * 用户管理首页
     */
    public function index()
    {
        $search = input('search', '', 'text');
        if (!empty($search)) {
            $uids = $this->MemberModel
                ->where('username', 'like', '%' . $search . '%')
                ->whereOr('nickname', 'like', '%' . $search . '%')
                ->whereOr('mobile', 'like', '%' . $search . '%')
                ->whereOr('email', 'like', '%' . $search . '%')
                ->column('uid');
            if (!empty($uids)) {
                $map[] = ['uid', 'in', $uids];
            } else {
                $map[] = ['nickname', 'like', '%' . $search . '%'];
            }
        }

        //排序
        $sort = input('order', 'create_time', 'text');
        $order = '';
        if ($sort == 'uid') {
            $order = 'uid desc';
        }
        if ($sort == 'create_time') {
            $order = 'create_time desc';
        }
        if ($sort == 'last_login_time') {
            $order = 'last_login_time desc';
        }
        if ($sort == 'login') {
            $order = 'login desc';
        }
        $map[] = ['status', '>=', 0];
        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        $list = $this->MemberModel->where($map)->order($order)->paginate($rows);
        $pager = $list->render();
        $list = $list->toArray();
        $list_arr = $list['data'];

        foreach ($list_arr as $key => $v) {
            //处理用户头像
            if (empty($list_arr[$key]['avatar'])) {
                $list_arr[$key]['avatar'] = $list_arr[$key]['avatar64'] = $list_arr[$key]['avatar128'] = $list_arr[$key]['avatar256'] = $list_arr[$key]['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
            } else {
                $list_arr[$key]['avatar64'] = get_thumb_image($list_arr[$key]['avatar'], 64, 64);
                $list_arr[$key]['avatar128'] = get_thumb_image($list_arr[$key]['avatar'], 128, 128);
                $list_arr[$key]['avatar256'] = get_thumb_image($list_arr[$key]['avatar'], 256, 256);
                $list_arr[$key]['avatar512'] = get_thumb_image($list_arr[$key]['avatar'], 512, 512);
            }
            //获取权限组
            $auth_g_id = Db::name('auth_group_access')->where(['uid' => $v['uid']])->select()->toArray();
            foreach ($auth_g_id as $k => $val) {
                $auth_group = Db::name('auth_group')->where(['id' => $val['group_id']])->value('title');
                $list_arr[$key]['auth_group'][$k]['title'] = $auth_group;
            }
            unset($k);
            unset($val);
        }

        int_to_string($list_arr);
        if (request()->isAjax()) {
            $list['data'] = $list_arr;
            return $this->success('success', $list);
        }
        $this->setTitle('用户列表');
        View::assign('title', '用户列表');
        View::assign('pager', $pager);
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
        $data['password'] = user_md5('123456', config('auth.auth_key'));
        $res = $this->MemberModel->where('uid', 'in', $uids)->update(['password' => $data['password']]);
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
        $uid = input('uid', 0, 'intval');
        if (request()->isPost()) {
            $data = input();
            // 初始化写入数据
            if(!empty($uid)){
                $member_data['uid'] = $uid;
            }
            $member_data['nickname'] = $data['nickname'];
            $member_data['avatar'] = $data['avatar'];
            $member_data['username'] = $data['username'];
            $member_data['email'] = $data['email'];
            $member_data['mobile'] = $data['mobile'];
            $member_data['realname'] = $data['realname'];
            $member_data['sex'] = intval($data['sex']);
            $member_data['status'] = intval($data['status']);
            try{
                if ($member_data['username'] == '' && $member_data['email'] == '' && $member_data['mobile'] == '') {
                    throw new Exception('用户名、邮箱、手机号，至少填写一项！');
                }
                $this->checkNickname($member_data['nickname'], $uid);
                $this->checkUsername($member_data['username'], $uid);
                $this->checkEmail($member_data['email'], $uid);
                $this->checkMobile($member_data['mobile'], $uid);
            }catch(Exception $e){
                return $this->error($e->getMessage());
            }
            // 写入数据并返回UID
            $uid= $this->MemberModel->edit($member_data);

            /* 积分 start*/
            $data_score = [];
            foreach ($data as $key => $val) {
                if (substr($key, 0, 5) == 'score') {
                    $data_score[$key] = $val;
                }
            }
            foreach ($data_score as $key => $val) {
                $user_score = query_user($uid, array($key));
                // 值相同跳过
                if (intval($val) == intval($user_score[$key])) {
                    continue;
                } else {
                    //写入积分
                    $this->MemberModel->where('uid', $uid)->update($data_score);
                    //写积分变化日志
                    if (intval($val) > intval($user_score[$key])) {
                        $action = 'inc';
                        $value = intval($val) - intval($user_score[$key]);
                    } else {
                        $action = 'dec';
                        $value = intval($user_score[$key]) - intval($val);
                    }
                    $scoreLogModel = new ScoreLogModel();
                    $scoreLogModel->addScoreLog($uid, cut_str('score', $key, 'l'), $action, $value, '', 0, get_nickname(is_login()) . '后台调整');
                }
            }
            /* 积分 end*/

            /*用户组 start*/
            $authGroup = new AuthGroup();
            $authGroup->addToGroup($uid, $data['auth_group']);
            /*用户组END*/

            return $this->success('保存成功');
        } else {
            // 获取用户数据
            $member = $this->MemberModel->where('uid', '=', $uid)->find();

            // 扩展资料
            $field_group = Db::name('field_group')->where('status', '=', 1)->select();

            $fields_list = [];
            if (!empty($field_group) && !empty($member)) {
                $field_group = $field_group->toArray();
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
            }

            $builder = new AdminConfigBuilder();
            $builder->title('用户资料管理');
            $builder->keyUid()
                ->keySingleImage('avatar', '头像', '')
                ->keyText('username', '用户名')
                ->keyText('email', '邮箱')
                ->keyText('mobile', '手机号')
                ->keyText('nickname', '昵称')
                ->keyText('realname', '真实姓名')
                ->keyRadio('sex', '性别', '', [0 => '不详', 1 => '男', 2 => '女'])
                ->keyRadio('status', '状态', '', [1 => '启用', 0 => '禁用']);

            $field_key = ['uid', 'avatar', 'username', 'email', 'mobile', 'nickname', 'realname', 'sex', 'status'];
            foreach ($fields_list as $vt) {
                $field_key[] = $vt['field_name'];
            }

            /* 积分设置 */
            $scoreTypeModel = new ScoreTypeModel();
            $field = $scoreTypeModel->getTypeList([['status', '=', 1]]);
            $score_key = [];
            foreach ($field as $vf) {
                $score_key[] = 'score' . $vf['id'];
                $builder->keyText('score' . $vf['id'], $vf['title']);
            }
            /*积分设置end*/

            /*权限组*/
            // 用户拥有的权限组
            $auth = Db::name('auth_group_access')->where(['uid' => $uid])->select();
            $temp_auth_group_arr = [];
            foreach ($auth as $key => $val) {
                $temp_auth_group_arr[] = $val['group_id'];
            }
            if(empty($member)){
                $member['auth_group'] = 1;
            }else{
                $member['auth_group'] = implode(',', $temp_auth_group_arr);
            }
            
            // 系统设置启用的权限组
            $auth_group = Db::name('auth_group')->where('status', '=', 1)->select();
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
        $uid = input('uid', 0, 'intval');
        $map[] = ['uid', '=', $uid];
        $member = $this->MemberModel->where($map)->find()->toArray();

        View::assign('member', $member);

        return View::fetch();
    }

    /**
     * 会员状态修改
     */
    public function status($method = null)
    {
        $ids = input('ids');
        !is_array($ids) && $ids = explode(',', $ids);
        if (count(array_intersect(explode(',', config('auth.auth_administrator')), $ids)) > 0) {
            return $this->error('不允许对超管进行该操作');
        }
        if (empty($ids)) {
            return $this->error('请选择要操作的数据');
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
                (new MemberSyncModel())->where($map)->delete();
                (new MemberModel())->where($map)->delete();
                return $this->success('删除用户成功', '', 'refresh');
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
     * 验证昵称
     * @param $nickname
     */
    private function checkNickname($nickname, $uid)
    {
        $length = mb_strlen($nickname, 'utf8');
        if ($length == 0) {
            throw new Exception('请输入昵称');
        } else if ($length > config('system.NICKNAME_MAX_LENGTH', 32)) {
            throw new Exception('昵称不能超过' . config('system.NICKNAME_MAX_LENGTH', 32) . '个字');
        } else if ($length < config('system.NICKNAME_MIN_LENGTH', 2)) {
            throw new Exception('昵称不能少于' . config('system.NICKNAME_MIN_LENGTH', 2) . '个字');
        }
        $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
        if (!$match) {
            throw new Exception('昵称只允许中文、字母、下划线和数字');
        }
        //验证唯一性
        $map_nickname[] = ['nickname', '=', $nickname];
        $map_nickname[] = ['uid', '<>', $uid];
        $had_nickname = Db::name('Member')->where($map_nickname)->count();

        if ($had_nickname > 0) {
            throw new Exception('昵称已被占用');
        }
        $denyName = Db::name("config")->where(['name' => 'USER_NAME_BAOLIU'])->value('value');
        if ($denyName != '') {
            $denyName = explode(',', $denyName);
            foreach ($denyName as $val) {
                if (!is_bool(strpos($nickname, $val))) {
                    throw new Exception('该昵称已被禁用');
                }
            }
        }
    }

    /**
     * 验证用户名
     */
    private function checkUsername($username, $uid)
    {
        //验证唯一性
        $map[] = ['username', '=', $username];
        $map[] = ['uid', '<>', $uid];
        $has = Db::name('Member')->where($map)->count();
        if ($has) {
            throw new Exception('用户名已被占用');
        }

        if($uid != 1){
            $denyName = Db::name("config")->where(['name' => 'USER_NAME_BAOLIU'])->value('value');
            if ($denyName != '') {
                $denyName = explode(',', $denyName);
                foreach ($denyName as $val) {
                    if (!is_bool(strpos($username, $val))) {
                        throw new Exception('该用户名已被禁用');
                    }
                }
            }
        }
    }

    private function checkEmail($email, $uid)
    {
        if (!empty($email)) {
            if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $email)) {
                throw new Exception('请正确填写邮箱！');
            }

            //验证唯一性
            $map[] = ['email', '=', $email];
            $map[] = ['uid', '<>', $uid];
            $has = Db::name('Member')->where($map)->count();
            if ($has) {
                throw new Exception('邮箱已被占用');
            }
        }
    }

    private function checkMobile($mobile, $uid)
    {
        if (!empty($mobile)) {
            if (!preg_match("/^\d{11}$/", $mobile)) {
                throw new Exception('请正确填写手机号！');
            }

            //验证唯一性
            $map[] = ['mobile', '=', $mobile];
            $map[] = ['uid', '<>', $uid];
            $has = Db::name('Member')->where($map)->count();
            if ($has) {
                throw new Exception('手机号已被占用');
            }
        }
    }

    /**
     * Modal 选择用户信息
     * @return \think\response\View
     * @throws \think\db\exception\DbException
     */
    public function chooseUser()
    {
        $search = input('search', '', 'text');
        $oauth_type = input('oauth_type', '', 'text'); //授权条件

        //用户名或昵称查询
        $uids = $this->MemberModel
            ->where('uid', '=', $search)
            ->where('username', 'like', '%' . $search . '%')
            ->whereOr('nickname', 'like', '%' . $search . '%')
            ->whereOr('mobile', 'like', '%' . $search . '%')
            ->whereOr('email', 'like', '%' . $search . '%')
            ->column('uid');
        if (!empty($uids)) {
            $map[] = ['m.uid', 'in', $uids];
        } else {
            $map[] = ['m.nickname', 'like', '%' . (string)$search . '%'];
        }

        $map[] = ['m.status', '>=', 0];

        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        if (empty($oauth_type)) {
            $list = $this->MemberModel->alias('m')->where($map)->order('uid', 'desc')->paginate($rows);
        } else {
            $map[] = ['ms.type', '=', $oauth_type];
            $list = $this->MemberModel->alias('m')->join('member_sync ms', 'm.uid = ms.uid')->where($map)->order('m.uid', 'desc')->paginate($rows);
        }

        $pager = $list->render();
        $list = $list->toArray();
        $list_arr = $list['data'];

        foreach ($list_arr as $key => $v) {
            //处理用户头像
            if (empty($list_arr[$key]['avatar'])) {
                $list_arr[$key]['avatar'] = $list_arr[$key]['avatar64'] = $list_arr[$key]['avatar128'] = $list_arr[$key]['avatar256'] = $list_arr[$key]['avatar512'] = request()->domain() . '/static/common/images/default_avatar.jpg';
            } else {
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

        return View::fetch('_choose_user');
    }
}
