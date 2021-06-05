<?php

namespace app\admin\controller;

use think\App;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use think\facade\Db;
use think\facade\Cache;
use think\Response;
use think\Validate;
use app\common\controller\Base;

/**
 * 控制器基础类
 */
class Admin extends Base
{
    /**
     * 鉴权中间件，只能放在基类内，避免获取不到控制器和方法名
     * @var array
     */
    protected $middleware = [
        \app\admin\middleware\CheckRule::class
    ];
    
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 系统设置
     * @var array
     */
    protected $system = [];

    /**
     * 当前模块管理菜单
     * @var array
     */
    protected $menu = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 当前模块管理菜单
        //$this->menu = $this->getMenus();
        //$this->system = Cache::get('DB_CONFIG_DATA');
        // 控制器初始化
        $this->initialize();

    }

    public function initialize()
    {
        
    }

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     */
    final public function getMenus()
    {   
        $module = app('http')->getName();
        $controller = request()->controller();
        $action = request()->action();
        $menuModel = new \app\admin\model\Menu();
        $moduleModel = new \app\common\model\Module();

        $menus  =   session('ADMIN_MENU_LIST'.$controller);
        if (empty($menus)) {
            // 获取主菜单
            $where['pid'] = '0';
            //$where['hide'] = 0;
            
            $menus['main'] = $menuModel->getLists($where);
            
            $menus['main'] = $menus['main']->toArray();
            
            $menus['child'] = []; //设置子节点

            //当前菜单
            $current = $menuModel->whereRaw('`url` = "'.$module .'/'. $controller .'/'. $action .'"')->find();
            
            if ($current) {
                //获取顶级菜单数据
                $nav = $menuModel->getPath($current['id']);
                $nav_current_id = $nav[0]['id'];
                
                
                foreach ($menus['main'] as $key => $item) {

                    //如果是模块菜单获取模块信息
                    if($item['module'] != '' || !empty($item['module'])){
                        $module_info = $moduleModel->getModule($item['module']);
                    }
                    
                    if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                        $this->error(lang('_CLASS_CONTROLLER_ERROR_PARAM_',array('menus'=>$menus)));
                    }
                    if (stripos($item['url'], $module) !== 0) {
                        $item['url'] = $module . '/' . $item['url'];
                    }
                    // 判断主菜单权限
                    /*
                    if (!$this->checkRule($item['url'], 2, null)) {
                        unset($menus['main'][$key]);
                        continue;//继续循环
                    }*/

                    // 获取当前主菜单的子菜单项
                    if ($item['id'] == $nav_current_id) {
                        $menus['main'][$key]['class'] = 'active';
                        //生成child树
                        $groups = Db::name('Menu')->where(['pid'=>$item['id']])->distinct(true)->field("`group`")->order('sort asc')->select();

                        if ($groups) {
                            $groups = array_column($groups, 'group');
                        } else {
                            $groups = [];
                        }

                        //获取二级分类的合法url
                        $where = [];
                        $where['pid'] = $item['id'];
                        $where['hide'] = 0;
                        
                        $second_urls = $menuModel->getLists($where);

                        if (!$this->is_root) {
                            // 检测菜单权限
                            $to_check_urls = array();
                            foreach ($second_urls as $key => $to_check_url) {
                                if (stripos($to_check_url, $module) !== 0) {
                                    $rule = $module . '/' . $to_check_url;
                                } else {
                                    $rule = $to_check_url;
                                }
                                if ($this->checkRule($rule, AuthRuleModel::RULE_URL, null))
                                    $to_check_urls[] = $to_check_url;
                            }
                        }
                        // 按照分组生成子菜单树
                        $map = [];
                        foreach ($groups as $g) {
                            $map = array('group' => $g);
                            if (isset($to_check_urls)) {
                                if (empty($to_check_urls)) {
                                    // 没有任何权限
                                    continue;
                                } else {
                                    $map['url'] = array('in', $to_check_urls);
                                }
                            }
                            $map['pid'] = $item['id'];
                            $map['hide'] = 0;
                            
                            $menuList = Db::name('Menu')->where($map)->field('id,pid,title,url,icon,tip')->order('sort asc')->select();

                            $menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                        }
                    }
                }
            }
        }

        return $menus;
    }

    protected function checkUpdate()
    {
        if ($this->system('AUTO_UPDATE')) {
            $can_update = 1;
        } else {
            $can_update = 0;
        }
    }

    /**
     * 获取版本号
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    private function localVersion()
    {   
        $path = PUBLIC_PATH . '/../data/version.ini';
        $version = file_get_contents($path);

        return $version;
    }
}
