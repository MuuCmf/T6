<?php

namespace app\admin\controller;

use think\facade\View;
use app\common\model\AuthRule;
use app\common\model\Menu as MenuModel;
use app\common\model\Module as ModuleModel;
use app\common\service\Tree;

/**
 * 后台管理菜单控制器
 */
class Menu extends Admin
{
    protected $MenuModel;
    protected $ModuleModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->MenuModel = new MenuModel();
        $this->ModuleModel = new ModuleModel();
    }

    /**
     * 后台权限菜单列表
     * @return none
     */
    public function index()
    {
        $title = input('title', '', 'text');

        $list_map = [];
        if (!empty($title)) {
            $list_map[] = ['title', 'like', '%' . $title . '%'];
        }
        $list_map[] = ['type', '=', '0'];

        $result_list = $this->MenuModel->where($list_map)->order('sort asc')->select();
        if(!empty($result_list)){
            $result_list = $result_list->toArray();
        }
        foreach ($result_list as &$val) {
            $val = $this->MenuModel->handle($val);
        }
        unset($val);
        
        // 转树结构
        $list = list_to_tree($result_list, 'id', 'pid', '_child', '0');

        // ajax请求返回数据
        if (request()->isAjax()) {
            return $this->success('success', $list);
        }

        View::assign('list', $list);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->setTitle('后台菜单管理');

        return View::fetch();
    }

    /**
     * 获取分组后的管理菜单树
     */
    public function tree()
    {
        $app = input('app', '', 'text');
        // 获取主菜单
        if (!empty($app)) {
            $where[] = ['module', '=', $app];
        }
        $where[] = ['pid', '=', '0'];
        $main_menu = $this->MenuModel->where($where)->field('id,pid,title,url,icon,tip,type,module')->order('sort', 'asc')->select()->toArray();

        foreach ($main_menu as $key => $item) {

            if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                return $this->error('控制器基类{$menus}属性元素配置有误');
            }

            // 判断主菜单权限
            if (!$this->isRoot && !$this->checkRule($item['url'], get_uid(), AuthRule::RULE_MAIN, null)) {
                unset($main_menu[$key]);
                continue; //继续循环
            }

            // 获取当前主菜单的子菜单项
            $groups = $this->MenuModel->where('pid', $item['id'])->order('sort asc')->column('group');
            $groups = array_unique($groups);
            //获取二级分类的合法url
            $where = [];
            $where['pid'] = $item['id'];
            $where['hide'] = 0;
            $second_urls = $this->MenuModel->where($where)->order('sort asc')->select()->toArray();

            if (!$this->isRoot) {
                // 检测菜单权限
                $to_check_urls = [];
                foreach ($second_urls as $key => $to_check_url) {
                    $rule = $to_check_url['url'];
                    if ($this->checkRule($rule, get_uid(), 1, null)) {
                        $to_check_urls[] = $to_check_url['url'];
                    }
                }
            }
            // 按照分组生成子菜单树
            foreach ($groups as $k => $g) {
                $map = [];
                $map[] = ['group', '=', $g];
                if (isset($to_check_urls)) {
                    if (empty($to_check_urls)) {
                        // 没有任何权限
                        continue;
                    } else {
                        $map[] = ['url', 'in', $to_check_urls];
                    }
                }
                $map[] = ['pid', '=', $item['id']];
                $map[] = ['hide', '=', 0];
                $menu_list = $this->MenuModel->where($map)->field('id,pid,title,url,icon,tip,type,module')->order('sort asc')->select()->toArray();

                if ($menu_list) {
                    $menus['group'] = $g;
                    $menus['lists'] = list_to_tree($menu_list, 'id', 'pid', 'operater', $item['id']);
                }
                $main_menu[$key]['child'][] = $menus;
            }
        }

        return $this->success('success', $main_menu);
    }

    /**
     * 后台权限菜单接口列表
     * @return none
     */
    public function list()
    {
        $title = input('title', '', 'text');
        $pid  = input('pid', '0', 'text');
        $module = input('module', '', 'text');

        if ($title) {
            $map['title'] = ['like', '%' . $title . '%'];
        }
        if ($module) {
            $map['module'] = $module;
        }
        $map['pid'] =   $pid;
        $list       =   $this->MenuModel->where($map)->order('sort asc,id asc')->select();

        //输出
        return $this->result(200, 'SUCCESS', $list);
    }

    /**
     * 新增/编辑配置
     */
    public function edit()
    {

        if (request()->isPost()) {
            $data = input('');
            if ($data['title'] == '') {
                return $this->error('菜单标题不能为空');
            }
            if ($data['url'] == '') {
                return $this->error('菜单链接不能为空');
            }

            $res = $this->MenuModel->edit($data);
            if ($res) {
                //记录行为
                action_log('update_menu', 'Menu', $data['id'], is_login());
                return $this->success('保存成功', $res, cookie('__forward__'));
            } else {
                return $this->error('保存失败');
            }
        } else {
            $id = input('id', '0', 'text');
            $pid = input('pid', '0', 'text');
            View::assign('pid', $pid);
            $info = [];
            /* 获取数据 */
            $info = $this->MenuModel->where(['id' => $id])->find();

            if (empty($info)) {
                $map['id'] = input('pid');
                $info = $this->MenuModel->where($map)->field('module,pid,hide,type')->find();
                $info['pid'] = input('pid', '0', 'text');
            }
            View::assign('info', $info);

            // 获取菜单
            $menus = $this->MenuModel->where('type', 0)->order('sort asc,id asc')->select()->toArray();
            $tree = new Tree();
            $menus = $tree->toFormatTree($menus, 'title', 'id', 'pid', '0');
            $menus = array_merge([
                0 => ['id' => '0', 'title_show' => '顶级菜单']
            ], $menus);

            View::assign('Menus', $menus);
            View::assign('Modules', $this->ModuleModel->getAll());

            $this->setTitle('菜单编辑');
            // 输出页面
            return View::fetch();
        }
    }

    /**
     * 删除后台菜单
     */
    public function del()
    {
        $id = array_unique((array)input('id/a', []));

        if (empty($id)) {
            return $this->error('参数错误');
        }
        //判断是否有下级菜单
        $res =  $this->MenuModel->where('pid', 'in', $id)->select()->toArray();

        if (!empty($res)) {
            return $this->error('下级菜单不为空');
        }
        //开始移除菜单
        if ($this->MenuModel->where('id', 'in', $id)->delete()) {
            //记录行为
            action_log('update_menu', 'Menu', $id, is_login());
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    /**
     * 菜单导入
     */
    public function import()
    {
        if (request()->isPost()) {
            $tree = input('post.tree');
            $lists = explode(PHP_EOL, $tree);

            if ($lists == array()) {
                return $this->error('请按格式填写批量导入的菜单，至少一个菜单');
            } else {
                $pid = input('post.pid');
                foreach ($lists as $key => $value) {
                    $record = explode('|', $value);
                    if (count($record) == 2) {
                        $this->MenuModel->insert([

                            'id' => create_guid(),
                            'title' => $record[0],
                            'url' => $record[1],
                            'pid' => $pid,
                            'sort' => 0,
                            'hide' => 0,
                            'tip' => '',
                            'group' => '',
                        ]);
                    }
                }
                return $this->success('导入成功', '', url('index', ['pid' => $pid]));
            }
        } else {
            $this->setTitle('菜单导入');
            $pid = (string)input('get.pid');
            View::assign('pid', $pid);
            $data = $this->MenuModel->where('id', '=', $pid)->find();

            View::assign('data', $data);
            return View::fetch();
        }
    }

    /**
     * 菜单排序
     */
    public function sort()
    {

        if (request()->isPost()) {
            $ids = input('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = $this->MenuModel->where(['id' => $value])->update(['sort' => $key + 1]);
            }
            if ($res !== false) {
                return $this->success('排序成功');
            } else {
                return $this->error('排序失败');
            }
        } else {
            $map[] = ['pid', '=', '0'];
            $map[] = ['type', '=', 0];
            $list = $this->MenuModel->where($map)->field('id,title')->order('sort asc')->select();

            View::assign('list', $list);
            $this->setTitle('菜单排序');
            // 输出页面
            return View::fetch();
        }
    }

    public function sidebar()
    {
        //当前管理菜单
        $admin_menu = $this->getMenus();

        return $this->success('SUCCESS', $admin_menu);
    }
}
