<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\Db;
use think\Request;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminSortBuilder;
use app\common\Model\MemberModel;

/**
 * 后台用户控制器
 */
class User extends Admin
{
    public function _initialize()
    {
        parent::_initialize();
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

                $uid = Db::name('ucenter_member')->where($mapUsername)->value('id');
                if($uid){
                    $map['uid'] = $uid;
                }else{
                    $map['nickname'] = ['like', '%' . (string)$search . '%'];
                }
            }
            //邮箱查询
            if($email){
                $mapEmail['email'] = array('like', '%' . $email . '%');
                $map['uid'] = Db::name('ucenter_member')->where($mapEmail)->value('id');
            }
            //手机查询
            if($mobile){
                $mapMobile['mobile'] = array('like', '%' . $nickname . '%');
                $map['uid'] = Db::name('ucenter_member')->where($mapMobile)->value('id');
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

        $map['status'] = array('egt', 0);
        list($list,$page) = $this->commonLists('Member', $map, $order);
        $list_arr = $list->toArray()['data'];
        foreach($list_arr as $key=>$v){
            //初始化ext键，避免报错
            $list_arr[$key]['username']='';
            $list_arr[$key]['email']='';
            $list_arr[$key]['mobile']='';

            $ext_info = query_user(['nickname','username','mobile','email'],$v['uid']);
            $list_arr[$key] = array_merge($list_arr[$key],$ext_info);
            //获取权限组
            $auth_g_id = collection(Db::name('auth_group_access')->where(['uid'=>$v['uid']])->select())->toArray();
            foreach($auth_g_id as $k=>$val){
                $auth_group = Db::name('auth_group')->where(['id'=>$val['group_id']])->value('title');
                $list_arr[$key]['auth_group'][$k]['title'] = $auth_group;
            }
            unset($k);
            unset($val);
        }

        int_to_string($list_arr);

        $this->setTitle(lang('_USER_INFO_'));
        $this->assign('title','用户列表');
        $this->assign('_list', $list_arr);
        return $this->fetch();
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
            $this->error(lang('_ERROR_USER_RESET_SELECT_').lang('_EXCLAMATION_'));
        }

        $ucModel = Db::name('UcenterMember');

        $data['password'] = user_md5('123456',config('database.auth_key'));

        $res = $ucModel->where(['id' => ['in', $uids]])->setField('password',$data['password']);

        if ($res) {
            $this->success(lang('_SUCCESS_PW_RESET_').lang('_EXCLAMATION_'));
        } else {
            $this->error(lang('_ERROR_PW_RESET_'));
        }
    }

    /**用户资料详情修改
     * @param string $uid
     * @author 大蒙<59262424@qq.com>
     */
    public function expandinfo_details()
    {   
        
        if (request()->isPost()) {
            
            $data = input('post.');
            $uid = $data['id'];
            /* 修改积分 */
            foreach ($data as $key => $val) {
                if (substr($key, 0, 5) == 'score') {
                    $data_score[$key] = $val;
                }
            }
            
            $res_score = Db::name('Member')->where(['uid' => $uid])->update($data_score);
            
            foreach ($data_score as $key => $val) {
                $value = query_user(array($key), $uid);
                if ($val == $value[$key]) {
                    continue;
                }
                //写积分变化日志
                model('ucenter/Score')->addScoreLog($data['id'], cut_str('score', $key, 'l'), 'to', $val, '', 0, get_nickname(is_login()) . lang('_BACKGROUND_ADJUSTMENT_'));
                //清理用户积分缓存
                model('ucenter/Score')->cleanUserCache($data['id'], cut_str('score', $key, 'l'));
            }
            /* 修改积分 end*/

            /*用户组设置*/
            //如果设置了默认积分会新增积分
            model('AuthGroup')->addToGroup($uid, $data['auth_group']);
            /*用户组END*/

            //基础设置
            $map['uid'] = $uid;
            $aNickname = $data['nickname'];
            $this->checkNickname($aNickname, $uid);
            $user['nickname'] = $aNickname;

            $rs_member = Db::name('Member')->where($map)->update($user);

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
            $ucenterMemberData = [
                'id' => $uid,
                'username' => $aUsername,
                'email' => $aEmail,
                'mobile' => $aMobile
            ];

            $rs_register = Db::name('UcenterMember')->update($ucenterMemberData);
            //用户名、邮箱、手机变成可编辑内容end

            //清理用户缓存
            clean_query_user_cache($uid, 'expand_info');
            if ($rs_member || $rs_register) {
                $this->success(lang('_SAVE_SUCCESS_').lang('_EXCLAMATION_'));
            } else {
                $this->error(lang('_SAVE_FAILED_').lang('_EXCLAMATION_'));
            }
        } else {
            $uid = input('uid');
            $map['uid'] = $uid;
            $map['status'] = ['>=', 0];
            $member = Db::name('Member')->where($map)->find();
            $member['id'] = $member['uid'];
            $ucenterMember = Db::name('UcenterMember')->where(array('id' => $uid))->field('username,email,mobile')->find();
            $member['username']=$ucenterMember['username'];
            $member['email']=$ucenterMember['email'];
            $member['mobile']=$ucenterMember['mobile'];

            //扩展信息查询
            $map_profile['status'] = 1;
            $field_group = Db::name('field_group')->where($map_profile)->select();
            $field_group_ids = array_column($field_group, 'id');
            $map_profile['profile_group_id'] = array('in', $field_group_ids);
            $fields_list = Db::name('field_setting')->where($map_profile)->field('id,field_name,form_type')->select();
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
            $builder->title(lang('_USER_EXPAND_INFO_DETAIL_'));
            $builder->keyId()
                    ->keyText('email','邮箱')
                    ->keyText('mobile','手机号')
                    ->keyText('username', lang('_USER_NAME_'))
                    ->keyText('nickname', lang('_NICKNAME_'));

            $field_key = array('id', 'username','email','mobile', 'nickname');
            foreach ($fields_list as $vt) {
                $field_key[] = $vt['field_name'];
            }

            /* 积分设置 */
            $field = model('ucenter/Score')->getTypeList(['status' => 1]);

            $score_key = [];
            foreach ($field as $vf) {
                $score_key[] = 'score' . $vf['id'];
                $builder->keyText('score' . $vf['id'], $vf['title']);
            }

            $score_data = Db::name('Member')->where(['uid' => $uid])->field(implode(',', $score_key))->find();

            $member = array_merge($member, $score_data);
            /*积分设置end*/

            /*权限组*/
            $auth_group = collection(Db::name('auth_group')->where(['status'=>1])->select())->toArray();
            $auth_group_options = [];
            foreach ($auth_group as $val) {
               $auth_group_options[$val['id']] = $val['title'];
            }
            $builder->keyCheckBox('auth_group', lang('_USER_GROUP_'), lang('_MULTI_OPTIONS_'), $auth_group_options);

            /*权限组end*/
            $builder->data($member);
            
            $builder
                ->group(lang('_BASIC_SETTINGS_'), implode(',', $field_key))
                ->group(lang('_SETTINGS_SCORE_'), implode(',', $score_key))
                ->group(lang('_USER_GROUP_'),'auth_group')
                ->buttonSubmit('', lang('_SAVE_'))
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
            $this->error(lang('_ERROR_NICKNAME_INPUT_').lang('_PERIOD_'));
        } else if ($length > modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG')) {
            $this->error(lang('_ERROR_NICKNAME_1_'). modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').lang('_ERROR_NICKNAME_2_').lang('_PERIOD_'));
        } else if ($length < modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG')) {
            $this->error(lang('_ERROR_NICKNAME_LENGTH_1_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').lang('_ERROR_NICKNAME_2_').lang('_PERIOD_'));
        }
        $match = preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $nickname);
        if (!$match) {
            $this->error(lang('_ERROR_NICKNAME_LIMIT_').lang('_PERIOD_'));
        }
        //验证唯一性
        $map_nickname['nickname'] = $nickname;
        $map_nickname['uid'] = ['neq', $uid];
        $had_nickname = Db::name('Member')->where($map_nickname)->count();

        if ($had_nickname) {
            $this->error(lang('_ERROR_NICKNAME_USED_').lang('_PERIOD_'));
        }
        $denyName = Db::name("Config")->where(array('name' => 'USER_NAME_BAOLIU'))->value('value');
        if ($denyName != '') {
            $denyName = explode(',', $denyName);
            foreach ($denyName as $val) {
                if (!is_bool(strpos($nickname, $val))) {
                    $this->error(lang('_ERROR_NICKNAME_FORBIDDEN_').lang('_PERIOD_'));
                }
            }
        }
    }

    /**扩展用户信息分组列表
     */
    public function profile()
    {
        $r = 20;
        $map['status'] = array('egt', 0);
        $profileList = Db::name('field_group')->where($map)->order("sort asc")->paginate($r);
        $totalCount = Db::name('field_group')->where($map)->count();
        $page = $profileList->render();

        $profileList = $profileList->toArray()['data'];

        $builder = new AdminListBuilder();

        $builder->title(lang('_GROUP_EXPAND_INFO_LIST_'));
        //$builder->meta_title = lang('_GROUP_EXPAND_INFO_');

        $builder
            ->buttonNew(url('editProfile', array('id' => '0')))
            ->buttonDelete(url('changeProfileStatus', array('status' => '-1')))
            ->setStatusUrl(url('changeProfileStatus'))
            ->buttonSort(url('sortProfile'));

        $builder
            ->keyId()
            ->keyText('profile_name', lang('_GROUP_NAME_'))
            ->keyText('sort', lang('_SORT_'))
            ->keyTime("createTime", lang('_CREATE_TIME_'))
            ->keyBool('visiable', lang('_PUBLIC_IF_'));

        $builder
            ->keyStatus()
            ->keyDoActionEdit('User/field?id=###', lang('_FIELD_MANAGER_'))
            ->keyDoActionEdit('User/editProfile?id=###', lang('_EDIT_'));

        $builder->data($profileList);
        $builder->page($page);
        $builder->display();
    }

    /**
     * 扩展分组排序
     */
    public function sortProfile($ids = null)
    {
        if (request()->isPost()) {
            $builder = new AdminSortBuilder();
            $builder->doSort('Field_group', $ids);
        } else {
            $map['status'] = array('egt', 0);
            $list = Db::name('field_group')->where($map)->order("sort asc")->select();
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['profile_name'];
            }
            $builder = new AdminSortBuilder();
            $builder->title(lang('_GROUPS_SORT_'));
            $builder->data($list);
            $builder
                    ->buttonSubmit(Url('sortProfile'))
                    ->buttonBack();
            $builder->display();
        }
    }

    /**
     * 扩展字段列表
     * @param $id
     */
    public function field($id)
    {
        $r = 20;
        $profile = Db::name('field_group')->where('id=' . $id)->find();
        $map['status'] = array('egt', 0);
        $map['profile_group_id'] = $id;
        $field_list = Db::name('field_setting')->where($map)->order("sort asc")->select();
        $totalCount = Db::name('field_setting')->where($map)->count();

        $type_default = array(
            'input' => lang('_ONE-WAY_TEXT_BOX_'),
            'radio' => lang('_RADIO_BUTTON_'),
            'checkbox' => lang('_CHECKBOX_'),
            'select' => lang('_DROP-DOWN_BOX_'),
            'time' => lang('_DATE_'),
            'textarea' => lang('_MULTI_LINE_TEXT_BOX_')
        );
        $child_type = array(
            'string' => lang('_STRING_'),
            'phone' => lang('_PHONE_NUMBER_'),
            'email' => lang('_MAILBOX_'),
            'number' => lang('_NUMBER_'),
            'join' => lang('_RELATED_FIELD_')
        );
        foreach ($field_list as &$val) {
            $val['form_type'] = $type_default[$val['form_type']];
            if($val['child_form_type']) {
                $val['child_form_type'] = $child_type[$val['child_form_type']];
            }
            
        }
        unset($val);

        $builder = new AdminListBuilder();
        $builder->title('【' . $profile['profile_name'] . '】' .lang('_FIELD_MANAGEMENT_'));

        $builder
        ->buttonNew(url('editFieldSetting', array('id' => '0', 'profile_group_id' => $id)))
        ->buttonDelete(url('setFieldSettingStatus', array('status' => '-1')))
        ->setStatusUrl(url('setFieldSettingStatus'))
        ->buttonSort(url('sortField', array('id' => $id)))
        ->button(lang('_RETURN_'), array('href' => Url('profile')));

        $builder
        ->keyId()
        ->keyText('field_name', lang('_FIELD_NAME_'))
        ->keyBool('visiable', lang('_OPEN_YE_OR_NO_'))
        ->keyBool('required', lang('_WHETHER_THE_REQUIRED_'))
        ->keyText('sort', lang('_SORT_'))
        ->keyText('form_type', lang('_FORM_TYPE_'))
        ->keyText('child_form_type', lang('_TWO_FORM_TYPE_'))
        ->keyText('form_default_value', lang('_DEFAULT_'))
        ->keyText('validation', lang('_FORM_VERIFICATION_MODE_'))
        ->keyText('input_tips', lang('_USER_INPUT_PROMPT_'));
        $builder
        ->keyTime("createTime", lang('_CREATE_TIME_'))
        ->keyStatus()
        ->keyDoAction('User/editFieldSetting?profile_group_id=' . $id . '&id=###', lang('_EDIT_'));

        $builder->data($field_list);
        $builder->pagination($totalCount, $r);
        $builder->display();
    }

    /**
     * 分组排序
     * @param $id
     */
    public function sortField($id = '', $ids = null)
    {
        if (request()->isPost()) {
            $builder = new AdminSortBuilder();
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
            $builder->meta_title = $profile['profile_name'] . lang('_FIELD_SORT_');
            $builder->data($list);
            $builder->buttonSubmit(Url('sortField'))->buttonBack();
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
    public function editFieldSetting()
    {
        if (request()->isPost()) {

            $data = input('');
            if ($data['field_name'] == '') {
                $this->error(lang('_FIELD_NAME_CANNOT_BE_EMPTY_'));
            }

            //当表单类型为以下三种是默认值不能为空判断@MingYang
            $form_types = array('radio', 'checkbox', 'select');
            if (in_array($data['form_type'], $form_types)) {
                if ($data['form_default_value'] == '') {
                    $this->error($data['form_type'] . lang('_THE_DEFAULT_VALUE_OF_THE_FORM_TYPE_CAN_NOT_BE_EMPTY_'));
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
                    $this->error(lang('_THIS_GROUP_ALREADY_HAS_THE_SAME_NAME_FIELD_PLEASE_USE_ANOTHER_NAME_'));
                }
                $data['status'] = 1;
                $data['createTime'] = time();
                $data['sort'] = 0;
                $res = Db::name('field_setting')->strict(true)->insertGetId($data);
            }
            
            $this->success(
                $data['id'] == '' ? lang('_ADD_FIELD_SUCCESS_') : lang('_EDIT_FIELD_SUCCESS_'), 
                url('field', ['id' => $data['profile_group_id']])
            );
        } else {
            $id = input('id');
            
            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $field_setting = Db::name('field_setting')->where('id=' . $id)->find();
                $builder->title(lang('_MODIFY_FIELD_INFORMATION_'));

            } else {
                $builder->title(lang('_ADD_FIELD_').lang('_NEW_FIELD_'));

                $field_setting['profile_group_id'] = $profile_group_id;
                $field_setting['visiable'] = 1;
                $field_setting['required'] = 1;
            }
            $type_default = array(
                'input' => lang('_ONE-WAY_TEXT_BOX_'),
                'radio' => lang('_RADIO_BUTTON_'),
                'checkbox' => lang('_CHECKBOX_'),
                'select' => lang('_DROP-DOWN_BOX_'),
                'time' => lang('_DATE_'),
                'textarea' => lang('_MULTI_LINE_TEXT_BOX_')
            );
            $child_type = array(
                'string' => lang('_STRING_'),
                'phone' => lang('_PHONE_NUMBER_'),
                'email' => lang('_MAILBOX_'),
                //增加可选择关联字段类型 @MingYang
                'join' => lang('_RELATED_FIELD_'),
                'number' => lang('_NUMBER_')
            );
            $builder
            ->keyReadOnly("id", lang('_LOGO_'))
            ->keyReadOnly('profile_group_id', lang('_GROUP_ID_'))
            ->keyText('field_name', lang('_FIELD_NAME_'))
            ->keySelect('form_type', lang('_FORM_TYPE_'), '', $type_default)
            ->keySelect('child_form_type', lang('_TWO_FORM_TYPE_'), '', $child_type)
            ->keyTextArea('form_default_value', "多个值用'|'分割开,格式【字符串：男|女，数组：1:男|2:女，关联数据表：字段名|表名】开")
            ->keyText('validation', lang('_FORM_VALIDATION_RULES_'), '例：min=5&max=10')
            ->keyText('input_tips', lang('_USER_INPUT_PROMPT_'), lang('_PROMPTS_THE_USER_TO_ENTER_THE_FIELD_INFORMATION_'))
            ->keyBool('visiable', lang('_OPEN_YE_OR_NO_'))
            ->keyBool('required', lang('_WHETHER_THE_REQUIRED_'));

            $builder
            ->data($field_setting);
            $builder
            ->buttonSubmit(Url('editFieldSetting'), $id == 0 ? lang('_ADD_') : lang('_MODIFY_'))
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
    public function setFieldSettingStatus($ids, $status)
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
            $this->error(lang('_PLEASE_CHOOSE_TO_OPERATE_THE_DATA_'));
        }
        $id = is_array($id) ? $id : explode(',', $id);
        Db::name('field_group')->where(array('id' => array('in', $id)))->setField('status', $status);
        if ($status == -1) {
            $this->success(lang('_DELETE_SUCCESS_'));
        } else if ($status == 0) {
            $this->success(lang('_DISABLE_SUCCESS_'));
        } else {
            $this->success(lang('_ENABLE_SUCCESS_'));
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
                $this->error(lang('_GROUP_NAME_CANNOT_BE_EMPTY_'));
            }
            if ($id != '') {
                $res = Db::name('field_group')->where(['id'=>$id])->update($data);
            } else {
                $map['profile_name'] = $profile_name;
                $map['status'] = array('egt', 0);
                if (Db::name('field_group')->where($map)->count() > 0) {
                    $this->error(lang('_ALREADY_HAS_THE_SAME_NAME_GROUP_PLEASE_USE_THE_OTHER_GROUP_NAME_'));
                }
                $data['status'] = 1;
                $data['createTime'] = time();
                $res = Db::name('field_group')->insert($data);
            }
            if ($res) {
                $this->success($id == '' ? lang('_ADD_GROUP_SUCCESS_') : lang('_EDIT_GROUP_SUCCESS_'), Url('profile'));
            } else {
                $this->error($id == '' ? lang('_ADD_GROUP_FAILURE_') : lang('_EDIT_GROUP_FAILED_'));
            }
        } else {
            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $profile = Db::name('field_group')->where(['id'=>$id])->find();
                $builder->title(lang('_MODIFIED_GROUP_INFORMATION_'));
            } else {
                $builder->title(lang('_ADD_EXTENDED_INFORMATION_PACKET_'));
                $builder->meta_title = lang('_NEW_GROUP_');
            }
            $builder
                ->keyReadOnly("id", lang('_LOGO_'))
                ->keyText('profile_name', lang('_GROUP_NAME_'))
                ->keyBool('visiable', lang('_OPEN_YE_OR_NO_'));

            $builder
                ->data($profile);
            $builder
                ->buttonSubmit(url('editProfile'), $id == 0 ? lang('_ADD_') : lang('_MODIFY_'))
                ->buttonBack();
            $builder->display();
        }

    }

    

    /**
     * 会员状态修改
     */
    public function changeStatus($method = null)
    {
        $id = array_unique((array)input('id/a', 0));
        if (count(array_intersect(explode(',', config('USER_ADMINISTRATOR')), $id)) > 0) {
            $this->error(lang('_DO_NOT_ALLOW_THE_SUPER_ADMINISTRATOR_TO_PERFORM_THE_OPERATION_'));
        }
        $id = is_array($id) ? implode(',', $id) : $id;
        if (empty($id)) {
            $this->error(lang('_PLEASE_CHOOSE_TO_OPERATE_THE_DATA_'));
        }

        $map['uid'] = ['in', $id];

        switch (strtolower($method)) {
            case 'forbiduser':
                $this->forbid('Member', $map);
                break;
            case 'resumeuser':
                $this->resume('Member', $map);
                break;
            case 'deleteuser':
                $this->delete('Member', $map);
                break;
            default:
                $this->error(lang('_ILLEGAL_'));

        }
    }


    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = lang('_USER_NAME_MUST_BE_IN_LENGTH_') . modC('USERNAME_MIN_LENGTH', 2, 'USERCONFIG') . '-' . modC('USERNAME_MAX_LENGTH', 32, 'USERCONFIG') . lang('_BETWEEN_CHARACTERS_');
                break;
            case -2:
                $error = lang('_USER_NAME_IS_FORBIDDEN_TO_REGISTER_');
                break;
            case -3:
                $error = lang('_USER_NAME_IS_OCCUPIED_');
                break;
            case -4:
                $error = lang('_PASSWORD_LENGTH_MUST_BE_BETWEEN_6-30_CHARACTERS_');
                break;
            case -5:
                $error = lang('_MAILBOX_FORMAT_IS_NOT_CORRECT_');
                break;
            case -6:
                $error = lang('_MAILBOX_LENGTH_MUST_BE_BETWEEN_1-32_CHARACTERS_');
                break;
            case -7:
                $error = lang('_MAILBOX_IS_PROHIBITED_TO_REGISTER_');
                break;
            case -8:
                $error = lang('_MAILBOX_IS_OCCUPIED_');
                break;
            case -9:
                $error = lang('_MOBILE_PHONE_FORMAT_IS_NOT_CORRECT_');
                break;
            case -10:
                $error = lang('_MOBILE_PHONES_ARE_PROHIBITED_FROM_REGISTERING_');
                break;
            case -11:
                $error = lang('_PHONE_NUMBER_IS_OCCUPIED_');
                break;
            case -12:
                $error = lang('_USER_NAME_MY_RULE_').lang('_EXCLAMATION_');
                break;
            default:
                $error = lang('_UNKNOWN_ERROR_');
        }
        return $error;
    }

    /**
     * 积分列表
     * @return [type] [description]
     */
    public function scoreList()
    {
        //读取数据
        $map = array('status' => array('GT', -1));
        $model = model('ucenter/Score');
        $list = $model->getTypeList($map);

        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title(lang('_INTEGRAL_TYPE_'))
            ->suggest(lang('_CANNOT_DELETE_ID_4_'))
            ->buttonNew(Url('editScoreType'))
            ->setStatusUrl(Url('setTypeStatus'))->buttonEnable()->buttonDisable()->button(lang('_DELETE_'), array('class' => 'btn ajax-post tox-confirm', 'data-confirm' => '您确实要删除积分分类吗？（删除后对应的积分将会清空，不可恢复，请谨慎删除！）', 'url' => Url('delType'), 'target-form' => 'ids'))
            ->keyId()->keyText('title', lang('_NAME_'))
            ->keyText('unit', lang('_UNIT_'))->keyStatus()->keyDoActionEdit('editScoreType?id=###')
            ->data($list)
            ->display();
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

    public function setTypeStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('ucenter_score_type', $ids, $status);

    }

    public function delType($ids)
    {
        $model = model('ucenter/Score');
        $res = $model->delType($ids);
        if ($res) {
            $this->success(lang('_DELETE_SUCCESS_'));
        } else {
            $this->error(lang('_DELETE_FAILED_'));
        }
    }

    public function editScoreType()
    {
        $aId = input('id', 0, 'intval');
        $model = model('ucenter/Score');
        if (request()->isPost()) {
            $data['title'] = input('post.title', '', 'text');
            $data['status'] = input('post.status', 1, 'intval');
            $data['unit'] = input('post.unit', '', 'text');

            if ($aId != 0) {
                $data['id'] = $aId;
                $res = $model->editType($data);
            } else {
                $res = $model->addType($data);
            }
            if ($res) {
                $this->success(($aId == 0 ? lang('_ADD_') : lang('_EDIT_')) . lang('_SUCCESS_'));
            } else {
                $this->error(($aId == 0 ? lang('_ADD_') : lang('_EDIT_')) . lang('_FAILURE_'));
            }
        } else {
            $builder = new AdminConfigBuilder();
            if ($aId != 0) {
                $type = $model->getType(array('id' => $aId));
            } else {
                $type = array('status' => 1, 'sort' => 0);
            }
            $builder
                ->title(($aId == 0 ? lang('_NEW_') : lang('_EDIT_')) . lang('_INTEGRAL_CLASSIFICATION_'))
                ->keyId()
                ->keyText('title', lang('_NAME_'))
                ->keyText('unit', lang('_UNIT_'))
                ->keySelect('status', lang('_STATUS_'), null, array(-1 => lang('_DELETE_'), 0 => lang('_DISABLE_'), 1 => lang('_ENABLE_')))
                ->data($type)
                ->buttonSubmit(Url('editScoreType'))
                ->buttonBack()
                ->display();
        }
    }

    private function mb_unserialize($serial_str) 
    { 
      $serial_str= preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str ); 
      $serial_str= str_replace("\r", "", $serial_str); 
      return unserialize($serial_str);     
    }
}
