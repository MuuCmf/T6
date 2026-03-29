<?php

namespace app\admin\controller;

use think\facade\Db;
use app\common\model\Member as MemberModel;
use app\common\model\AuthRule;
use app\common\model\AuthGroup;
use app\common\model\AuthGroupAccess;

class Auth extends Admin
{
    protected $AuthGroupModel;
    protected $AuthGroupAccessModel;
    protected $MemberModel;

    public function __construct()
    {
        parent::__construct();
        $this->AuthGroupModel = new AuthGroup();
        $this->AuthGroupAccessModel = new AuthGroupAccess();
        $this->MemberModel = new MemberModel();
    }

    /**
     * 权限组列表
     */
    public function group()
    {
        // 加载方式 all 全量查询  page 分页查询
        $load = input('load', 'page', 'text');

        // 查询条件
        $map[] = ['module', '=', 'admin'];
        $map[] = ['status', 'in', [0, 1]];

        $rows = input('rows', 15, 'intval');
        $keyword = input('keyword', '', 'text');

        if (!empty($keyword)) {
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $fields = '*';
        if ($load == 'page') {
            // 分页查询
            $list = $this->AuthGroupModel->getListByPage($map, 'id desc', $fields, $rows);
            if (!empty($list)) {
                $pager = $list->render();
                $list = $list->toArray();
                // 获取权限组用户数量
                foreach ($list['data'] as $key => $item) {
                    $list['data'][$key]['user_count'] = $this->AuthGroupAccessModel->memberCount($item['id']);
                    $list['data'][$key]['status_str'] = $this->AuthGroupModel->_status[$item['status']];
                }
            }
        } else {
            // 全量查询
            $list = $this->AuthGroupModel->where($map)->select();
            if (!empty($list)) {
                $list = $list->toArray();
                // 处理状态字段
                foreach ($list as $key => $item) {
                    $list[$key]['status_str'] = $this->AuthGroupModel->_status[$item['status']];
                }
            }
            $pager = '';
        }

        // json response
        return $this->success('success!', $list);
    }

    /**
     * 编辑用户组
     */
    public function groupEdit()
    {
        $id = input('id', 0, 'intval');
        if (request()->isPost()) {
            $data = input();
            $data['module'] = 'admin';
            $data['type'] = AuthGroup::TYPE_ADMIN;
            if (empty($data['id'])) {
                $data['rules'] = '';
            }

            if ($data) {
                $res = $this->AuthGroupModel->edit($data);
                if ($res === false) {
                    return $this->error('操作失败');
                } else {
                    return $this->success('操作成功!', $res, cookie('__forward__'));
                }
            } else {
                return $this->error('操作失败');
            }
        } else {
            $auth_group = $this->AuthGroupModel->where(['module' => 'admin', 'type' => AuthGroup::TYPE_ADMIN])->find((int)$id);

            return $this->success('success!', $auth_group);
        }
    }

    /**
     * 用户组状态修改
     */
    public function groupStatus()
    {
        $ids = input('id');
        if (empty($ids)) {
            return $this->error('请选择要操作的数据');
        }
        !is_array($ids) && $ids = explode(',', $ids);

        $status = input('status', 0, 'intval');

        // 系统默认组禁止删除
        if ($status == -1 && (in_array(1, $ids) || in_array(2, $ids) || in_array(3, $ids))) {
            return $this->error('系统默认组禁止删除');
        }

        $title = '更新';
        if ($status == 0) {
            $title = '禁用';
        }
        if ($status == 1) {
            $title = '启用';
        }
        if ($status == -1) {
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->AuthGroupModel->where('id', 'in', $ids)->update($data);
        if ($res) {
            return $this->success($title . '成功');
        } else {
            return $this->error($title . '失败');
        }
    }

    /**
     * 真实删除权限组
     */
    public function groupDelete()
    {
        $id = input('id', 0, 'intval');

        if (empty($id)) {
            return $this->error('参数错误');
        }

        if (in_array($id, [1, 2, 3])) {
            return $this->error('系统默认组禁止删除');
        }

        $res = $this->AuthGroupModel->where('id', $id)->delete();

        if ($res) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    /**
     * 用户组授权用户列表
     * @author 大蒙 <59262424@qq.com>
     */
    public function user()
    {
        $group_id = input('group_id', 0, 'intval');
        if (empty($group_id)) {
            return $this->error('参数错误');
        }

        // 搜索关键词 支持昵称/手机号/uid
        $keyword = input('keyword', '', 'text');

        $l_table = AuthGroup::MEMBER;
        $r_table = AuthGroup::AUTH_GROUP_ACCESS;
        $where = [
            ['a.group_id', '=', $group_id],
            ['m.status', '>=', 0]
        ];
        if (!empty($keyword)) {
            $where[] = function($query) use ($keyword) {
                $query->where('m.nickname', 'like', '%' . $keyword . '%')
                      ->whereOr('m.mobile', 'like', '%' . $keyword . '%')
                      ->whereOr('m.uid', 'like', '%' . $keyword . '%');
            };
        }

        $list = Db::name($l_table)
            ->alias('m')
            ->join($r_table . ' a', 'm.uid = a.uid')
            ->where($where)
            ->field('m.uid,m.avatar,m.nickname,m.mobile,m.last_login_time,m.last_login_ip,m.status')
            ->order('m.uid desc')
            ->paginate([
                'list_rows' => 20,
                'query' => request()->param(),
            ], false);

        // 转数组
        $list = $list->toArray();
        foreach ($list['data'] as &$v) {
            $v = $this->MemberModel->info($v['uid'], '*');
        }
        unset($v);

        // json response
        return $this->success('success!', $list);
    }

    /**
     * 将用户从用户组中移除  入参:uid,group_id
     */
    public function removeFromGroup()
    {
        $uid = input('uid');
        $gid = input('group_id');
        if ($uid == is_login()) {
            return $this->error('禁止移除自身');
        }
        if (empty($uid) || empty($gid)) {
            return $this->error('参数错误');
        }

        if (!$this->AuthGroupModel->find($gid)) {
            return $this->error('该用户组不存在');
        }
        if ($this->AuthGroupModel->removeFromGroup($uid, $gid)) {
            return $this->success('操作成功');
        } else {
            return $this->error('操作失败');
        }
    }

    /**
     * 访问授权页面
     */
    public function access()
    {
        $group_id = input('group_id', 0, 'intval');
        // post请求
        if (request()->isPost()) {
            $data = input();

            // 处理规则
            if (isset($data['rules']) && !empty($data['rules'])) {
                sort($data['rules']);
                $data['rules'] = implode(',', array_unique($data['rules']));
            } else {
                $data['rules'] = '';
            }

            if ($data) {
                $res = $this->AuthGroupModel->edit($data);
                if ($res === false) {
                    return $this->error('操作失败');
                } else {
                    return $this->success('操作成功!', $res, cookie('__forward__'));
                }
            } else {
                return $this->error('操作失败');
            }
        }

        $group = Db::name('AuthGroup')->find($group_id);
        if (!$group) {
            return $this->error('用户组不存在');
        }

        $rules = $group['rules'];

        // 更新权限菜单
        $this->updateRules();
        // 所有权限节点树
        $node_tree = $this->returnNodes();

        $result = [
            'node_tree' => $node_tree,
            'group_rules' => $rules,
        ];

        // json response
        return $this->success('success!', $result);
    }

    /**
     * 后台节点配置的url作为规则存入auth_rule
     * 执行新节点的插入,已有节点的更新,无效规则的删除三项任务
     */
    protected function updateRules()
    {
        //需要新增的节点必然位于$nodes
        $nodes = Db::name('menu')
        ->field('id,pid,title,url,tip,hide,module')
        ->order('module asc, sort asc')
        ->select()
        ->toArray();

        $AuthRule = new AuthRule();
        //status全部取出,以进行更新
        $map = [['type', 'in', '1,2']];
        //需要更新和删除的节点必然位于$rules
        $rules = $AuthRule->where($map)->order('name')->select()->toArray();
        //构建insert数据
        $data = []; //保存需要插入和更新的新节点
        foreach ($nodes as $value) {
            $temp['name'] = $value['url'];
            $temp['title'] = $value['title'];
            $temp['module'] = $value['module'];
            if ($value['pid'] !== '0') {
                $temp['type'] = AuthRule::RULE_URL;
            } else {
                $temp['type'] = AuthRule::RULE_MAIN;
            }
            $temp['status'] = 1;
            $data[strtolower($temp['name'] . $temp['module'] . $temp['type'])] = $temp; //去除重复项
        }
        $update = []; //保存需要更新的节点
        $ids = []; //保存需要删除的节点的id
        foreach ($rules as $index => $rule) {
            $key = strtolower($rule['name'] . $rule['module'] . $rule['type']);
            if (isset($data[$key])) { //如果数据库中的规则与配置的节点匹配,说明是需要更新的节点
                $data[$key]['id'] = $rule['id']; //为需要更新的节点补充id值
                $update[] = $data[$key];
                unset($data[$key]);
                unset($rules[$index]);
                unset($rule['condition']);
                $diff[$rule['id']] = $rule;
            } elseif ($rule['status'] == 1) {
                $ids[] = $rule['id'];
            }
        }
        if (count($update)) {
            foreach ($update as $k => $row) {
                if ($row != $diff[$row['id']]) {
                    $AuthRule->where(['id' => $row['id']])->update($row);
                }
            }
        }

        if (count($ids)) {
            $AuthRule->where('id', 'in', $ids)->update(['status' => 0]);
            //删除规则是否需要从每个用户组的访问授权表中移除该规则?
        }

        if (count($data)) {
            $AuthRule->insertAll(array_values($data));
        }
        return true;
    }

    /**
     * 返回后台节点数据
     * @param boolean $tree 是否返回多维数组结构(生成菜单时用到),为false返回一维数组(生成权限节点时用到)
     * @retrun array
     * 注意,返回的主菜单节点数组中有'controller'元素,以供区分子节点和主节点
     * @author 大蒙<59262424@qq.com> 更新
     */
    protected function returnNodes()
    {
        $map = [
            ['auth_rule.status', '=', 1],
            ['auth_rule.type', 'in', [AuthRule::RULE_URL]],
        ];
        $list = Db::name('menu')
        ->alias('menu')
        ->join('auth_rule', 'menu.url = auth_rule.name')
        ->where($map)
        ->field('menu.id,menu.pid,menu.title,menu.url,menu.module,auth_rule.id as rule_id,auth_rule.title as rule_title')
        ->order('module asc, sort asc')
        ->select()
        ->toArray();

        foreach ($list as &$value) {
            $value = $this->check_url_re($value);
        }
        unset($value);

        //由于menu表id更改为字符串格式，root必须设置成字符串0
        $nodes = list_to_tree($list, 'id', 'pid', '_child', '0');

        return $nodes;
    }
    
    // 检查url是否以当前应用名称开头
    private function check_url_re($value = [])
    {
        if (empty($value['module']) || $value['module'] == '') {
            if (stripos($value['url'], app('http')->getName()) !== 0) {
                $value['url'] = app('http')->getName() . '/' . $value['url'];
            }
        }

        return $value;
    }

}
