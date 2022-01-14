<?php

namespace app\admin\controller;

use app\admin\lib\Upgrade as UpgradeServer;
use think\facade\Db;
use think\facade\View;
use app\common\service\Tree;
use app\admin\builder\AdminConfigBuilder;
use app\admin\model\Menu as MenuModel;
use app\common\model\Module as ModuleModel;


class Module extends Admin
{
    protected $MenuModel;
    protected $ModuleModel;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->MenuModel = new MenuModel();
        $this->ModuleModel = new ModuleModel();
    }

    /**
     * 模块管理列表首页
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function index()
    {
        $this->setTitle('应用管理');

        $aType = input('type', 'installed', 'text');
        View::assign('type', $aType);

        /*刷新模块列表时清空缓存*/
        $aRefresh = input('refresh', 0, 'intval');
        if ($aRefresh == 1) {
            cache('admin_modules', null);
            $this->ModuleModel->reload();
        }
        /*刷新模块列表时清空缓存 end*/
        switch($aType){

            case 'all':
                $map = [];
            break;

            case 'installed':
                $map[] = ['is_setup','=',1];
            break;

            case 'uninstalled':
                $map[] = ['is_setup','=',0];
            break;

            case 'core':
                $map[] = ['uninstall','=',0];
            break;
        };

        $upgradeServer = new UpgradeServer();
        $modules = $this->ModuleModel->getListByPage($map,'sort desc,id desc','*',20)->each(function ($item,$key) use($upgradeServer){
            //获取云端版本
            if ($item['is_com']){
                $result = $upgradeServer->cloudVersion([
                    'app_name' => $item['name'],
                    'appid'    => $item['appid']
                ]);
                $item['new_version'] = isset($result['data']['version']) ? $result['data']['version'] : $item['version'];
                $item['upgrade'] = get_upgrade_status($item['version'],$item['new_version']) ? 1 : 0;
            }else{
                $item['new_version'] = $item['version'];
                $item['upgrade'] = 0;
            }
            return $item;
        });
        $page = htmlspecialchars_decode($modules->render());
        View::assign('page', $page);
        View::assign('modules', $modules);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 输出页面
        return View::fetch();
    }

    /**
     * 编辑模块数据
     */
    public function edit()
    {
        $id = input('id',0,'intval');
        $title = $id ? "编辑" : "新建";
        if (request()->isPost()) {
            $data = input();
            
            $res = $this->ModuleModel->edit($data);
            if($res){
                return $this->success($title . '成功', $res, cookie('__forward__'));
            }else{
                return $this->error($title . '失败');
            }
        }

        if(!empty($id)){
            $data = $this->ModuleModel->getDataById($id);
        }
        View::assign('data',$data);

        return View::fetch();
    }

    /**
     * 卸载模块
     */
    public function uninstall()
    {
        $aId = input('id', 0, 'intval');
        $aNav = input('remove_nav', 0, 'intval');

        $module = $this->ModuleModel->getModuleById($aId);
        
        if (request()->isPost()) {
            $aWithoutData = input('withoutData', 1, 'intval');//是否保留数据
            $res = $this->ModuleModel->uninstall($aId, $aWithoutData);

            if ($res == true) {
                if ($aNav) {
                    Db::name('Channel')->where(['url' => $module['entry']])->delete();
                    cache('common_nav', null);
                }
                cache('admin_modules', null);
                //删除module表中记录
                $this->ModuleModel->where(['id' => $aId])->delete();
                return $this->success('卸载模块成功。','', cookie('__forward__'));
            } else {
                $this->error('卸载模块失败。' . $this->ModuleModel->error);
            }

        }else{
            $builder = new AdminConfigBuilder();
            $builder->title($module['alias'] . '——'.'卸载模块');
            $module['remove_nav'] = 1;
            $builder->keyReadOnly('id', '模块编号');
            $builder->suggest('<span class="text-danger">'.'请谨慎操作，此操作无法还原'.'</span>');
            $builder->keyReadOnly('alias', '卸载的模块');
            $builder->keyBool('withoutData', '是否保留模块数据'.'?', '默认保留模块数据');
            $builder->keyBool('remove_nav', '移除导航', '卸载后自动卸载掉对应的菜单，或者<a target="_blank" href="/index.php?s=/admin/channel/index.html">手动设置</a>');

            $module['withoutData'] = 1;
            $builder->data($module);
            $builder->buttonSubmit();
            $builder->buttonBack();
            $builder->display();
        }
    }

    /**
     * 安装模块
     * @return [type] [description]
     */
    public function install()
    {
        $aName = input('name', '', 'text');
        $module = $this->ModuleModel->getModule($aName);

        if (request()->isPost()) {
            //执行guide中的内容
            $res = $this->ModuleModel->install($module['id']);
            
            if ($res === true) {
                cache('ADMIN_MODULES_' . is_login(), null);
                $this->success('安装模块成功。', '', cookie('__forward__'));
            } else {
                $this->error('安装模块失败。' . $this->ModuleModel->error);
            }

        } else {
            $builder = new AdminConfigBuilder();
            $builder->title($module['alias'] . '-' . '模块安装向导');
            $builder
                ->keyId()
                ->keyReadOnly('name', '模块目录（唯一标识）')
                ->keyText('alias', '模块中文名不能为空。')
                ->keyReadOnly('version', '版本')
                ->keyText('icon', '图标')
                ->keyTextArea('summary', '模块介绍')
                ->keyReadOnly('developer', '开发者')
                ->keyText('entry', '前台入口')
                ->keyText('entry', '后台入口')
                ->keyRadio('mode', '安装模式', '', ['install' => '覆盖安装模式']);
            
            $builder->group('安装选项', 'name,alias,version,mode,add_nav');
            
            $module['mode'] = 'install';
            $builder->data($module);
            $builder->buttonSubmit();
            $builder->buttonBack();
            $builder->display();
        }
    }

    /**
     * 应用权限菜单首页
     * @return none
     */
    public function menu(){
        $app = input('app', '', 'text');
        View::assign('app',$app);
        $title = input('title','','text');
        $pid  = input('pid','0','text');
        View::assign('pid',$pid);
        $map = [];
        
        $list_map = [];
        if(!empty($app)){
            //获取上级数据
            $map['name'] = $app;
            $data = $this->ModuleModel->where($map)->find();
            View::assign('data',$data);
            $list_map[] = ['module', '=', $app];
        }
        
        if(!empty($title)){
            $list_map['title'] = ['like','%'.$title.'%'];
        }
        
        $list = $this->MenuModel->where($list_map)->order('sort asc')->select()->toArray();
        foreach($list as &$val){
            $val = $this->MenuModel->handle($val);
        }
        unset($val);
        // 转树结构
        $list = list_to_tree($list, 'id', 'pid', '_child', $pid);
        View::assign('list',$list);
        
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->setTitle('后台菜单管理');

        return View::fetch();
    }

    /**
     * 新增/编辑应用权限菜单
     */
    public function medit(){
        
        if(request()->isPost()){
            $data = input('');
            if($data['title'] == '') {
                return $this->error('菜单标题不能为空');
            }
            if($data['url'] == '') {
                return $this->error('菜单链接不能为空');
            }

            $res = $this->MenuModel->edit($data);
            if($res){
                //记录行为
                action_log('update_menu', 'Menu', $data['id'], is_login());
                return $this->success('保存成功', $res, cookie('__forward__'));
            } else {
                return $this->error('保存失败');
            }
            
        } else {
            $id = input('id','0','text');
            $pid = input('pid','0','text');
            View::assign('pid', $pid);
            $app = input('app', '', 'text');
            View::assign('app', $app);
            $info = [];
            /* 获取数据 */
            $info = $this->MenuModel->where(['id'=>$id])->find();

            if(empty($info)){
                $map['id'] = input('pid');
                $info = $this->MenuModel->where($map)->field('module,pid,hide,type')->find();
                $info['pid'] = input('pid','0','text');
            }
            View::assign('info', $info);

            $menus = $this->MenuModel->where('module','=',$app)->order('sort asc,id asc')->select()->toArray();
            $tree = new Tree();
            $menus = $tree->toFormatTree($menus,$title = 'title',$pk='id',$pid = 'pid',$root = '0');
            
            View::assign('Menus', $menus);
            $moduleModel = new ModuleModel();
            View::assign('Modules',$moduleModel->getAll());

            $this->setTitle('菜单编辑');

            return View::fetch();
        }
    }

    /**
     * 菜单删除
     */
    public function mdel()
    {
        $ids = input('ids/a');
        !is_array($ids)&&$ids=explode(',',$ids);

        $res = $this->MenuModel->where('id', 'in', $ids)->delete();
        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }  
    }

} 