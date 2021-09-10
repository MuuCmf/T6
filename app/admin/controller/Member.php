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
        $res = Db::name('Member')->where('uid','in', $uids)->update(['password' => $data['password']]);
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
            Db::name('Member')->where(['uid' => $uid])->update($data_score);
            
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
            $aUsername = $data['username'];
            $aEmail = $data['email'];
            $aMobile = $data['mobile'];
            if($aUsername ==''&&$aEmail==''&&$aMobile==''){
                $this->error('用户名、邮箱、手机号，至少填写一项！');
            }
            if($aEmail!=''){
                if (!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $aEmail)) {
                    $this->error('请正确填写邮箱！');
                }
            }
            if($aMobile!=''){
                if (!preg_match("/^\d{11}$/", $aMobile)) {
                    $this->error('请正确填写手机号！');
                }
            }
            $memberData = [
                'uid' => $uid,
                'username' => $aUsername,
                'email' => $aEmail,
                'mobile' => $aMobile,
                'nickname' => $aNickname,
            ];
            $rs_member = Db::name('Member')->where(['uid' => $uid])->update($memberData);
            // 用户名、邮箱、手机变成可编辑内容end

            // 清理用户缓存
            // clean_query_user_cache($uid, 'expand_info');
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
            $map_profile[] = ['profile_group_id', 'in', $field_group_ids];
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
                    ->keyText('email','邮箱')
                    ->keyText('mobile','手机号')
                    ->keyText('username', '用户名')
                    ->keyText('nickname', '昵称');

            $field_key = ['uid', 'username','email','mobile', 'nickname'];
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

    /**验证用户名
     * @param $nickname
     */
    private function checkNickname($nickname, $uid)
    {
        $length = mb_strlen($nickname, 'utf8');
        if ($length == 0) {
            $this->error('请输入昵称');
        } else if ($length > config('system.NICKNAME_MAX_LENGTH',32)) {
            $this->error('昵称不能超过'. config('system.NICKNAME_MAX_LENGTH',32).'个字');
        } else if ($length < config('system.NICKNAME_MIN_LENGTH',2)) {
            $this->error('昵称不能少于' . config('system.NICKNAME_MIN_LENGTH',2) . '个字');
        }
        $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
        if (!$match) {
            $this->error('昵称只允许中文、字母、下划线和数字');
        }
        //验证唯一性
        $map_nickname[] = ['nickname', '=', $nickname];
        $map_nickname[] = ['uid','<>', $uid];
        $had_nickname = Db::name('Member')->where($map_nickname)->count();

        if ($had_nickname) {
            $this->error('昵称已被人使用');
        }
        $denyName = Db::name("config")->where(['name' => 'USER_NAME_BAOLIU'])->value('value');
        if ($denyName != '') {
            $denyName = explode(',', $denyName);
            foreach ($denyName as $val) {
                if (!is_bool(strpos($nickname, $val))) {
                    $this->error('该昵称已被禁用');
                }
            }
        }
    }

    /**扩展用户信息分组列表
     */
    public function profile()
    {
        $r = 20;
        $map[] = ['status', '>', 0];
        $profileList = Db::name('field_group')->where($map)->order("sort asc")->paginate($r);
        $totalCount = Db::name('field_group')->where($map)->count();
        $page = $profileList->render();

        $profileList = $profileList->toArray()['data'];
        int_to_string($profileList);

        View::assign('title','扩展资料');
        View::assign('page',$page);
        View::assign('list', $profileList);
        
        return View::fetch();
    }

    /**
     * 扩展分组排序
     */
    public function sortProfile($ids = null)
    {
        if (request()->isPost()) {
            $builder = new AdminSortBuilder($this->app);
            $builder->doSort('Field_group', $ids);
        } else {
            $map['status'] = array('egt', 0);
            $list = Db::name('field_group')->where($map)->order("sort asc")->select();
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['profile_name'];
            }
            $builder = new AdminSortBuilder();
            $builder->title('组排序');
            $builder->data($list);
            $builder->buttonSubmit(url('sortProfile'))
                    ->buttonBack()
                    ->display();
        }
    }

    /**
     * 扩展字段列表
     * @param $id
     */
    public function field($id)
    {
        $r = 20;
        $profile = Db::name('field_group')->where('id', '=', $id)->find();
        $map[] = ['status', '>' , 0];
        $map[] = ['profile_group_id', '=', $id];
        $field_list = Db::name('field_setting')->where($map)->order("sort asc")->select()->toArray();
        $totalCount = Db::name('field_setting')->where($map)->count();

        // 表单类型
        $type_default = [
            'input' => '文本框',
            'radio' => '单选项',
            'checkbox' => '多选项',
            'select' => '下拉框',
            'time' => '日期',
            'textarea' => '文本域'
        ];
        // 二级表单类型
        $child_type = [
            'string' => '字符串',
            'phone' => '手机号',
            'email' => '邮箱',
            'number' => '数字',
            'join' => '关联字段'
        ];

        foreach ($field_list as &$val) {
            $val['form_type'] = $type_default[$val['form_type']];
            if($val['child_form_type']) {
                $val['child_form_type'] = $child_type[$val['child_form_type']];
            }
            
        }
        unset($val);

        View::assign('title','扩展资料');
        View::assign('list', $field_list);
        
        return View::fetch();
    }

    /**
     * 分组排序
     * @param $id
     */
    public function sortField($id = '', $ids = null)
    {
        if (request()->isPost()) {
            $builder = new AdminSortBuilder($this->app);
            $builder->doSort('FieldSetting', $ids);
        } else {
            $profile = Db::name('field_group')->where('id=' . $id)->find();
            $map['status'] = ['egt', 0];
            $map['profile_group_id'] = $id;
            $list = Db::name('field_setting')->where($map)->order("sort asc")->select();
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['field_name'];
            }
            $builder = new AdminSortBuilder();
            $builder->meta_title = $profile['profile_name'] . '排序失败';
            $builder->data($list);
            $builder->buttonSubmit(url('sortField'))->buttonBack();
            $builder->display();
        }
    }

    /**
     * 添加、编辑字段信息
     * @param $id
     * @param $profile_group_id
     * @param $field_name
     * @param $child_form_type
     * @param $visiable
     * @param $required
     * @param $form_type
     * @param $form_default_value
     * @param $validation
     * @param $input_tips
     */
    public function editField()
    {
        if (request()->isPost()) {

            $data = input('');
            if ($data['field_name'] == '') {
                return $this->error('字段名称不能为空！');
            }

            //当表单类型为以下三种是默认值不能为空判断@MingYang
            $form_types = array('radio', 'checkbox', 'select');
            if (in_array($data['form_type'], $form_types)) {
                if ($data['form_default_value'] == '') {
                    return $this->error($data['form_type'] . '表单类型默认值不能为空');
                }
            }
            
            if ($data['id'] != '') {
                Db::name('field_setting')->strict(true)->where(['id'=>$data['id']])->update($data);
                $res = Db::name('field_setting')->where(['id'=>$data['id']])->value('id');
            } else {
                $map['field_name'] = $field_name;
                $map['status'] = array('egt', 0);
                $map['profile_group_id'] = $profile_group_id;
                if (Db::name('field_setting')->where($map)->count() > 0) {
                    $this->error('该分组下已经有同名字段，请使用其他名称！');
                }
                $data['status'] = 1;
                $data['createTime'] = time();
                $data['sort'] = 0;
                $res = Db::name('field_setting')->strict(true)->insertGetId($data);
            }
            
            return $this->success(
                $data['id'] == '' ? '添加字段成功' : '编辑字段成功', 
                url('field', ['id' => $data['profile_group_id']])
            );
        } else {
            $id = input('id');
            
            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $field_setting = Db::name('field_setting')->where('id=' . $id)->find();
                $builder->title('修改字段信息');

            } else {
                $builder->title('添加字段' . '新增字段');

                $field_setting['profile_group_id'] = $profile_group_id;
                $field_setting['visiable'] = 1;
                $field_setting['required'] = 1;
            }
            $type_default = array(
                'input' => '单行文本框',
                'radio' => '单选按钮',
                'checkbox' => '多选框',
                'select' => '下拉选择框',
                'time' => '日期',
                'textarea' => '多行文本框'
            );
            $child_type = array(
                'string' => '字符串',
                'phone' => '手机号码',
                'email' => '邮箱',
                //增加可选择关联字段类型 @MingYang
                'join' => '关联字段',
                'number' => '数字'
            );
            $builder
            ->keyReadOnly("id", '标识')
            ->keyReadOnly('profile_group_id', '分组ID')
            ->keyText('field_name', '字段名称')
            ->keySelect('form_type', '表单类型', '', $type_default)
            ->keySelect('child_form_type', '二级表单类型', '', $child_type)
            ->keyTextArea('form_default_value', "多个值用'|'分割开,格式【字符串：男|女，数组：1:男|2:女，关联数据表：字段名|表名】开")
            ->keyText('validation', '表单验证规则', '例：min=5&max=10')
            ->keyText('input_tips', '用户输入提示', '提示用户如何输入该字段信息')
            ->keyBool('visiable', '是否公开')
            ->keyBool('required', '是否必填')
            ->data($field_setting)
            ->buttonSubmit(url('editField'), $id == 0 ? '新增' : '修改')
            ->buttonBack();

            $builder->display();
        }

    }

    /**
     * 设置字段状态：删除=-1，禁用=0，启用=1
     * @param $ids
     * @param $status
     * @author dameng<59262424@qq.com>
     */
    public function setFieldStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('field_setting', $ids, $status);
    }

    /**
     * 设置分组状态：删除=-1，禁用=0，启用=1
     * @param $status
     */
    public function changeProfileStatus($status)
    {
        $id = array_unique((array)input('ids', 0));
        if ($id[0] == 0) {
            return $this->error('请选择要操作的数据');
        }
        $id = is_array($id) ? $id : explode(',', $id);
        Db::name('field_group')->where(array('id' => array('in', $id)))->setField('status', $status);
        if ($status == -1) {
            return $this->success(lang('Delete'));
        } else if ($status == 0) {
            return $this->success(lang('Disable') . lang('Success'));
        } else {
            return $this->success(lang('Enable') . lang('Success'));
        }
    }

    /**
     * 添加、编辑分组信息
     * @param $id
     * @param $profile_name
     * @author dameng <59262424@qq.com>
     */
    public function editProfile($id = 0, $profile_name = '', $visiable = 1)
    {
        if (request()->isPost()) {
            $data['profile_name'] = $profile_name;
            $data['visiable'] = $visiable;
            if ($data['profile_name'] == '') {
                return $this->error('分组名称不能为空！');
            }
            if ($id != '') {
                $res = Db::name('field_group')->where(['id'=>$id])->update($data);
            } else {
                $map['profile_name'] = $profile_name;
                $map['status'] = array('egt', 0);
                if (Db::name('field_group')->where($map)->count() > 0) {
                    return $this->error('已经有同名分组，请使用其他分组名称！');
                }
                $data['status'] = 1;
                $data['create_time'] = time();
                $res = Db::name('field_group')->insert($data);
            }
            if ($res) {
                return $this->success($id == '' ? '新增分组成功' : '编辑分组成功', url('profile'));
            } else {
                return $this->error($id == '' ? '新增分组失败' : '编辑分组失败');
            }
        } else {
            $builder = new AdminConfigBuilder($this->app);
            if ($id != 0) {
                $profile = Db::name('field_group')->where(['id'=>$id])->find();
                $builder->title('修改分组信息');
            } else {
                $builder->title('添加扩展信息分组');
                $builder->meta_title = '新增分组';
            }
            $builder
                ->keyReadOnly("id", lang('Logo'))
                ->keyText('profile_name', '分组名称')
                ->keyBool('visiable', '是否公开');

            $builder
                ->data($profile);
            $builder
                ->buttonSubmit(url('editProfile'), $id == 0 ? lang('Add') : lang('Edit'))
                ->buttonBack();
            $builder->display();
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
            $user = query_user(null, $uid);

            return json($user);

        } else {

            return null;
        }

    }

}
