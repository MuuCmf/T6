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
