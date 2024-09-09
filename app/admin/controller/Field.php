<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminSortBuilder;

use app\common\model\Member as MemberModel;

/**
 * 后台扩展字段控制器
 */
class Field extends Admin
{
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 扩展用户信息分组列表
     */
    public function group()
    {
        $r = 20;
        $map[] = ['status', '>=', 0];
        $profileList = Db::name('field_group')->where($map)->order("sort asc")->paginate($r);
        $totalCount = Db::name('field_group')->where($map)->count();
        $page = $profileList->render();

        $profileList = $profileList->toArray()['data'];
        int_to_string($profileList);

        // ajax请求返回数据
        if (request()->isAjax()) {
            return $this->success('success', $profileList);
        }

        View::assign('title','扩展资料');
        View::assign('page',$page);
        View::assign('list', $profileList);
        
        return View::fetch();
    }

    /**
     * 添加、编辑分组信息
     * @param $id
     * @param $profile_name
     * @author dameng <59262424@qq.com>
     */
    public function editGroup($id = 0, $profile_name = '', $visiable = 1)
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
                return $this->success($id == '' ? '新增分组成功' : '编辑分组成功', '', url('group')->build());
            } else {
                return $this->error($id == '' ? '新增分组失败' : '编辑分组失败');
            }
        } else {

            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $profile = Db::name('field_group')->where(['id'=>$id])->find();
                $builder->title('修改分组信息');
            } else {
                $builder->title('添加扩展资料分组');
                $profile = [];
            }
            $builder
                ->keyReadOnly("id", 'ID')
                ->keyText('profile_name', '分组名称')
                ->keyBool('visiable', '是否公开');

            $builder
                ->data($profile);
            $builder
                ->buttonSubmit(url('editGroup'), $id == 0 ? lang('Add') : lang('Edit'))
                ->buttonBack()
                ->display();
        }
    }

    /**
     * 扩展分组排序
     */
    public function sortGroup($ids = null)
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
     * 设置分组状态：删除=-1，禁用=0，启用=1
     * @param $status
     */
    public function setGroupStatus()
    {
        $status = input('status',0 , 'intval');
        $id = array_unique((array)input('id', 0));
        if ($id[0] == 0) {
            return $this->error('请选择要操作的数据');
        }
        $id = is_array($id) ? $id : explode(',', $id);
        Db::name('field_group')->where('id' ,'in', $id)->update(['status'=> $status]);
        if ($status == -1) {
            return $this->success(lang('Delete'));
        } else if ($status == 0) {
            return $this->success(lang('Disable') . lang('Success'));
        } else {
            return $this->success(lang('Enable') . lang('Success'));
        }
    }

    /**
     * 扩展字段列表
     * @param $id
     */
    public function list()
    {
        $group_id = input('group_id', 0, 'intval');
        View::assign('group_id', $group_id);
        $group = Db::name('field_group')->where('id', '=', $group_id)->find();
        View::assign('group', $group);

        // 获取字段列表
        $map[] = ['status', '>' , 0];
        $map[] = ['group_id', '=', $group_id];
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


        foreach ($field_list as &$val) {
            $val['form_type'] = $type_default[$val['form_type']];
        }
        unset($val);

        View::assign('title','扩展资料');
        View::assign('list', $field_list);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        return View::fetch();
    }

    /**
     * 添加、编辑字段信息
     * @param $id
     * @param $group_id
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
            if ($data['field_alias'] == '') {
                return $this->error('字段描述不能为空！');
            }

            //当表单类型为以下三种是默认值不能为空判断@MingYang
            $form_types = array('radio', 'checkbox', 'select');
            if (in_array($data['form_type'], $form_types)) {
                if ($data['form_default_value'] == '') {
                    return $this->error($data['form_type'] . '表单类型默认值不能为空');
                }
            }
            
            if (!empty($data['id'])) {
                Db::name('field_setting')->strict(true)->where(['id'=>$data['id']])->update($data);
                $res = Db::name('field_setting')->where(['id'=>$data['id']])->value('id');
            } else {
                $map['field_name'] = $data['field_name'];
                $map['status'] = array('egt', 0);
                $map['group_id'] = $data['group_id'];
                if (Db::name('field_setting')->where($map)->count() > 0) {
                    return $this->error('该分组下已经有同名字段，请使用其他名称！');
                }
                $data['status'] = 1;
                $data['create_time'] = time();
                $data['sort'] = 0;
                $res = Db::name('field_setting')->strict(true)->insertGetId($data);
            }
            
            return $this->success($data['id'] == '' ? '添加字段成功' : '编辑字段成功', $res, cookie('__forward__'));

        } else {
            $id = input('id');
            $group_id = input('group_id');
            $builder = new AdminConfigBuilder();
            if (!empty($id)) {
                $field_setting = Db::name('field_setting')->where('id', '=', $id)->find();
                $builder->title('修改字段信息');

            } else {
                $builder->title('添加字段' . '新增字段');

                $field_setting['group_id'] = $group_id;
                $field_setting['visiable'] = 1;
                $field_setting['required'] = 1;
            }
            $type_default = array(
                'input' => '单行文本框',
                'textarea' => '多行文本框',
                'radio' => '单选框',
                'checkbox' => '多选框',
                'select' => '下拉选择框',
                'time' => '日期',
            );

            $builder
            ->keyReadOnly("id", 'ID')
            ->keyReadOnly('group_id', '分组ID')
            ->keyText('field_name', '字段名称','仅支持英文小写和"_"')
            ->keyText('field_alias', '字段描述')
            ->keySelect('form_type', '表单类型', '', $type_default)
            ->keyTextArea('form_default_value', "表单值选项", "多个值用'|'分割开，例：男|女")
            ->keyText('validation', '表单验证规则', "多个值用'|'分割开，例：require|max:25")
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
        return $builder->doSetStatus('field_setting', $ids, $status);
    }
}