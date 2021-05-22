<?php

namespace app\admin\controller;

use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;
use think\facade\Session;
use think\facade\Db;
use think\facade\Cache;
use think\Response;
use think\Validate;
use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;

/**
 * 控制器基础类
 */
abstract class Admin
{
    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = ['app\common\middleware\GlobleConfig'];

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
     * 后台基类 控制器
     */
    public $_seo;
    public $is_root;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->system = Cache::get('DB_CONFIG_DATA');
        // 控制器初始化
        $this->initialize();

    }

    public function initialize()
    {
        $this->_seo = ['title' => 'MuuCmf T5','Keywords' => '', 'Description' => ''];
        dump(config());
        // 判断登陆
        //$uid = $this->needLogin();
        dump($uid);
        // 检测访问权限
        $rule = strtolower(app('http')->getName() . '/' . Request()->controller() . '/' . Request()->action());
        //dump(AuthRule::RULE_URL);exit;
        if (!$this->checkRule($rule, ['in', '1,2'])) {
            return $this->error('无权限');
        }
        
        // 当前应用模块信息
        $module = model('common/Module')->getModule(app('http')->getName());
        // 当前模块管理菜单
        $menu = $this->getMenus();
        // 模块入口
        $all_module_list = model('common/Module')->getAll(['is_setup'=>1,'name'=>['neq','ucenter']]);
        // 本地版本
        View::assign(['version', $this->localVersion()]);
        $this->checkUpdate();
    }

    public function needLogin(){
        $uid = is_login();
        
        if (empty($uid)) {// 还没登录 跳转到登录页面
            return redirect('admin/common/login')->send();
        }
        return $uid;
    }
    /**
     * 权限检测
     * @param string $rule 检测的规则
     * @param string $mode check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type = AuthRule::RULE_URL, $mode = 'url')
    {
        if ($this->is_root) {
            return true;//管理员允许访问任何页面
        }
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \muucmf\Auth();
        }
        if (!$Auth->check($rule, is_login(), $type, $mode)) {
            return false;
        }
        return true;
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     *
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    protected function checkDynamic()
    {
        if ($this->is_root) {
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }


    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl()
    {
        if (Config::get('administrator_uid' == 1)) {
            return true;//管理员允许访问任何页面
        }

        $check = strtolower(request()->controller() . '/' . request()->action());
 
        return null;//需要检测节点权限
    }

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     */
    final public function getMenus()
    {   
        $module = request()->module();
        $controller = request()->controller();
        $action = request()->action();

        $menus  =   session('ADMIN_MENU_LIST'.$controller);
        if (empty($menus)) {
            // 获取主菜单
            $where['pid'] = '0';
            //$where['hide'] = 0;
            if (!$this->system('DEVELOP_MODE')) { // 是否开发者模式
                $where['is_dev'] = 0;
            }
            $menus['main'] = model('admin/Menu')->getLists($where);
            $menus['main'] = collection($menus['main'])->toArray();
            
            $menus['child'] = []; //设置子节点

            //当前菜单
            $current_map['url'] = [
                ['=', $module .'/'. $controller .'/'. $action],
                ['=', $controller .'/'. $action],
                'OR'
            ];
            $current = model('admin/Menu')->getDataByMap($current_map);

            if ($current) {
                //获取顶级菜单数据
                $nav = model('admin/Menu')->getPath($current['id']);
                $nav_current_id = $nav[0]['id'];
                //echo $nav_first_title;
                foreach ($menus['main'] as $key => $item) {

                    //如果是模块菜单获取模块信息
                    if($item['module'] != '' || !empty($item['module'])){
                        $module = model('common/Module')->getModule($item['module']);
                    }
                    
                    if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                        $this->error(lang('_CLASS_CONTROLLER_ERROR_PARAM_',array('menus'=>$menus)));
                    }
                    if (stripos($item['url'], request()->module()) !== 0) {
                        $item['url'] = request()->module() . '/' . $item['url'];
                    }
                    // 判断主菜单权限
                    if (!$this->is_root && !$this->checkRule($item['url'], AuthRuleModel::RULE_MAIN, null)) {
                        unset($menus['main'][$key]);
                        continue;//继续循环
                    }

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
                        if (!$this->system('DEVELOP_MODE')) { // 是否开发者模式
                            $where['is_dev'] = 0;
                        }
                        $second_urls = model('admin/Menu')->getLists($where);

                        if (!$this->is_root) {
                            // 检测菜单权限
                            $to_check_urls = array();
                            foreach ($second_urls as $key => $to_check_url) {
                                if (stripos($to_check_url, request()->module()) !== 0) {
                                    $rule = request()->module() . '/' . $to_check_url;
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
                            if (!$this->system('DEVELOP_MODE')) { // 是否开发者模式
                                $map['is_dev'] = 0;
                            }
                            
                            $menuList = Db::name('Menu')->where($map)->field('id,pid,title,url,icon,tip')->order('sort asc')->select();

                            $menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                        }
                    }
                }
            }
        }

        return $menus;
    }

    /** 
    * 操作成功跳转的快捷方法
    * @access protected
    * @param  mixed $msg 提示信息
    * @param  string $url 跳转的URL地址
    * @param  mixed $data 返回的数据
    * @param  integer $wait 跳转等待时间
    * @param  array $header 发送的Header信息
    * @return void
    */
   protected function success($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
   {
       if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
           $url = $_SERVER["HTTP_REFERER"];
       } elseif ($url) {
           $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : (string)$this->app->route->buildUrl($url);
       }

       $result = [
           'code' => 1,
           'msg' => $msg,
           'data' => $data,
           'url' => $url,
           'wait' => $wait,
       ];

       $type = $this->getResponseType();
       // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
       if ('html' == strtolower($type)) {
           $type = 'view';
           $response = Response::create($this->app->config->get('jump.dispatch_success_tmpl'), $type)->assign($result)->header($header);
       } else {
           $response = Response::create($result, $type)->header($header);
       }

       throw new HttpResponseException($response);
   }

   /**
    * 操作错误跳转的快捷方法
    * @access protected
    * @param  mixed $msg 提示信息
    * @param  string $url 跳转的URL地址
    * @param  mixed $data 返回的数据
    * @param  integer $wait 跳转等待时间
    * @param  array $header 发送的Header信息
    * @return void
    */
   protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
   {
       if (is_null($url)) {
           $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
       } elseif ($url) {
           $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : (string)$this->app->route->buildUrl($url);
       }

       $result = [
           'code' => 0,
           'msg' => $msg,
           'data' => $data,
           'url' => $url,
           'wait' => $wait,
       ];

       $type = $this->getResponseType();

       if ('html' == strtolower($type)) {
           $type = 'view';
           $response = Response::create($this->app->config->get('jump.dispatch_error_tmpl'), $type)->assign($result)->header($header);
       } else {
           $response = Response::create($result, $type)->header($header);
       }

       throw new HttpResponseException($response);
   }

   /**
    * 返回封装后的API数据到客户端
    * @access protected
    * @param  mixed $data 要返回的数据
    * @param  integer $code 返回的code
    * @param  mixed $msg 提示信息
    * @param  string $type 返回数据格式
    * @param  array $header 发送的Header信息
    * @return void
    */
   protected function result($data, $code = 0, $msg = '', $type = '', array $header = [])
   {
       $result = [
           'code' => $code,
           'msg' => $msg,
           'time' => time(),
           'data' => $data,
       ];

       $type = $type ?: $this->getResponseType();
       $response = Response::create($result, $type)->header($header);

       throw new HttpResponseException($response);
   }

   /**
    * URL重定向
    * @access protected
    * @param  string $url 跳转的URL表达式
    * @param  integer $code http code
    * @param  array $with 隐式传参
    * @return void
    */
   protected function redirect($url, $code = 302, $with = [])
   {
       $response = Response::create($url, 'redirect');

       $response->code($code)->with($with);

       throw new HttpResponseException($response);
   }

   /**
    * 获取当前的response 输出类型
    * @access protected
    * @return string
    */
   protected function getResponseType()
   {
       return $this->request->isJson() || $this->request->isAjax() ? 'json' : 'html';
   }

    protected function checkUpdate()
    {
        if ($this->system('AUTO_UPDATE')) {
            $can_update = 1;
        } else {
            $can_update = 0;
        }
        $this->assign('can_update', $can_update);
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
