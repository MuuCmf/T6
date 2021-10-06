<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\admin\builder\AdminConfigBuilder;
use app\common\model\Module as ModuleModel;

class Module extends Admin
{
    protected $moduleModel;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->moduleModel = new ModuleModel();
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
            $this->moduleModel->reload();
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

        $modules = $this->moduleModel->getListByPage($map,'sort desc,id desc','*',20);
        $page = htmlspecialchars_decode($modules->render());
        //dump($modules);exit;
        View::assign('page', $page);
        View::assign('modules', $modules);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        return View::fetch();
    }

    /**
     * 卸载模块
     */
    public function uninstall()
    {
        $aId = input('id', 0, 'intval');
        $aNav = input('remove_nav', 0, 'intval');

        $module = $this->moduleModel->getModuleById($aId);
        
        if (request()->isPost()) {
            $aWithoutData = input('withoutData', 1, 'intval');//是否保留数据
            $res = $this->moduleModel->uninstall($aId, $aWithoutData);

            if ($res == true) {
                if ($aNav) {
                    Db::name('Channel')->where(['url' => $module['entry']])->delete();
                    cache('common_nav', null);
                }
                cache('admin_modules', null);
                //删除module表中记录
                $this->moduleModel->where(['id' => $aId])->delete();
                return $this->success('卸载模块成功。','', cookie('__forward__'));
            } else {
                $this->error('卸载模块失败。' . $this->moduleModel->error);
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
        $module = $this->moduleModel->getModule($aName);

        if (request()->isPost()) {
            //执行guide中的内容
            $res = $this->moduleModel->install($module['id']);
            
            if ($res === true) {
                cache('ADMIN_MODULES_' . is_login(), null);
                $this->success('安装模块成功。', '', cookie('__forward__'));
            } else {
                $this->error('安装模块失败。' . $this->moduleModel->error);
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

} 