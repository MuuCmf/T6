<?php

namespace app\admin\controller;

use app\admin\lib\Cloud;
use think\facade\Db;
use think\facade\View;
use app\common\controller\Base;
use app\admin\model\Menu;
use app\admin\model\AuthRule;
use app\common\model\Module as ModuleModel;

/**
 * 控制器基础类
 */
class Admin extends Base
{

    protected $middleware = [
        // 鉴权中间件
        // 'app\common\middleware\CheckAuth::class'
    ];

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    protected $title;
    protected $moduleModel;
    public $isRoot;
    protected $menu = [];
    public $shopid = 0;//店铺Id，sass平台拓展
    public $app_name = ''; // 应用标识
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        $this->moduleModel = new ModuleModel();
        // 控制器初始化
        $this->initialize();
    }

    public function initialize()
    {
        // 判断登陆
        $this->needLogin();
        $this->recordRequest();
        $this->setTitle();
        // 当前系统域名
        View::assign('this_domain',request()->domain());
        // 当前模块、控制器及方法名
        $this->app_name = strtolower(app('http')->getName());
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        View::assign('this_app', $this->app_name);
        View::assign('this_controller',$controller );
        View::assign('this_action',$action);
        // 检查权限
        $rule = strtolower($this->app_name . '/' . $controller . '/' . $action);
        if(!$this->checkRule($rule, get_uid())){
            throw new \think\Exception('非法操作');
        }
        // 当前应用模块信息
        $module = $this->moduleModel->getModule($this->app_name);
        View::assign('module', $module);
        //当前管理菜单
        $admin_menu = $this->getMenus();
        View::assign('admin_menu', $admin_menu);
        //获取登录用户数据
        View::assign('auth_user',query_user(is_login()));
        //框架版本号
        View::assign('version',$this->version());
        //数据库版本号
        $mysql = Db::query("select version() as v;");
        $mysql_version = $mysql[0]['v'];
        View::assign('mysql_version',$mysql_version);
    }

    /**
     * 判断登录
     */
    public function needLogin(){

        $uid = is_login();
        if($uid == 1){
            $this->isRoot = 1;
        }
        if (!$uid) {// 还没登录 跳转到登录页面
            if($uid) return $uid;
            // 调整至前台登陆页
            $this->redirect(url('ucenter/common/login'));
        }

        return $uid;
    }

    /**
     * 已安装应用模块列表
     */
    protected function allModuleList()
    {
        $all_module_list = $this->moduleModel->getAll([
            ['is_setup', '=', 1],
            ['name', '<>','ucenter'],
            ['name', '<>','channel']
        ]);
        // 应用权限
        foreach($all_module_list as $key=>$item){
            // 判断主菜单权限
            if (!$this->isRoot && !$this->checkRule(strtolower($item['entry']), get_uid(), AuthRule::RULE_MAIN, null)) {
                unset($all_module_list[$key]);
                continue;//继续循环
            }
        }

        return $all_module_list;
    }

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     */
    final public function getMenus()
    {
        $module = input('param.module_name') ?? app('http')->getName();
        $controller = Request()->controller();
        $action = Request()->action();
        // 获取主菜单
        $where[] = ['pid','=','0'];
        $menuModel = new Menu();
        $menus['main'] = Db::name('menu')->where($where)->order('sort','asc')->select()->toArray();
        $menus['child'] = []; //设置子节点

        //当前菜单
        $current_map[] = [
            ['url','=', $module .'/'. $controller .'/'. $action],
        ];
        $current = Db::name('menu')->where($current_map)->find();
        
        //获取顶级菜单数据
        $nav_current_id = 0;
        if (input('?param.module_name') && $module != 'admin'){
            foreach ($menus['main'] as $m){
                if ($m['module'] == $module){
                    $nav_current_id = $m['id'];
                }
            }
        }elseif($current){
            $nav = $menuModel->getPath($current['id']);
            $nav_current_id = $nav[0]['id'];
        }
        if ($nav_current_id) {
            foreach ($menus['main'] as $key => $item) {

                //如果是模块菜单获取模块信息
                if(!empty($item['module']) || $item['module'] != 'admin'){
                    $app = $this->moduleModel->getModule($item['module']);
                }
                
                if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                    return $this->error('控制器基类{$menus}属性元素配置有误');
                }
                
                // 判断主菜单权限
                if (!$this->isRoot && !$this->checkRule($item['url'], get_uid(), AuthRule::RULE_MAIN, null)) {
                    unset($menus['main'][$key]);
                    continue;//继续循环
                }

                // 获取当前主菜单的子菜单项
                if ($item['id'] == $nav_current_id) {
                    $menus['main'][$key]['class'] = 'active';
                    //生成child树
                    $groups = Db::name('menu')->where('pid', $item['id'])->order('sort asc')->column('group');
                    $groups = array_unique($groups);
                    //获取二级分类的合法url
                    $where = [];
                    $where['pid'] = $item['id'];
                    $where['hide'] = 0;
                    $second_urls = Db::name('menu')->where($where)->order('sort asc')->select()->toArray();

                    if (!$this->isRoot) {
                        // 检测菜单权限
                        $to_check_urls = [];
                        foreach ($second_urls as $key => $to_check_url) {
                            $rule = $to_check_url['url'];
                            if ($this->checkRule($rule, get_uid(), 1, null)){
                                $to_check_urls[] = $to_check_url['url'];
                            }
                        }
                    }
                    // 按照分组生成子菜单树
                    
                    foreach ($groups as $k=>$g) {
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
                        
                        $menuList = Db::name('Menu')->where($map)->field('id,pid,title,url,icon,tip')->order('sort asc')->select()->toArray();
                        if ($menuList){
                            $menus['child'][$k]['group'] = $g;
                            $menus['child'][$k]['child'] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                        }
                    }
                }
            }
        }

        return $menus;
    }

        /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  userList.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: userList.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: userList.html?r=5
     *
     * @param sting|Table  $table 模型名或模型实例
     * @param array        $where where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order 排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base 基本的查询条件
     * @param boolean      $field 单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @return array|false
     * 返回数据集
     */
    public function commonLists($table, $where = [], $order = '', $base = [], $field = true)
    {
        $options = [];
        $REQUEST = (array)input('request.');
        if (is_string($table)) {
            $table = Db::name($table);
        }

        $pk = $table->getPk();
        if ($order === null) {
            //order置空
        } else if (isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']), ['desc', 'asc'])) {
            $options['order'] = '`' . $REQUEST['_field'] . '` ' . $REQUEST['_order'];
        } elseif ($order === '' && empty($options['order']) && !empty($pk)) {
            $options['order'] = $pk . ' desc';
        } elseif ($order) {
            $options['order'] = $order;
        }
        unset($REQUEST['_order'], $REQUEST['_field']);

        $options['where'] = array_filter(array_merge((array)$base, /*$REQUEST,*/
            (array)$where), function ($val) {
            
            if ( $val === null) {
                return false;
            } else {
                return true;
            }
        });
        if (empty($options['where'])) {
            unset($options['where']);
        }

        //$total = $table->where($options['where'])->count();

        if (input('r')!==null) {
            $listRows = (int)input('r');
        } else {
            $listRows = 20;
        }

        //获取列表
        $list = $table->where($options['where'])->order($options['order'])->paginate($listRows);
        // 获取分页显示
        $page = $list->render();
        $page = htmlspecialchars($page);

        return [$list,$page];
    }

    /**
     * 权限检测
     * @param string $rule 检测的规则
     * @param string $mode check模式
     * @return boolean
     */
    final protected function checkRule($rule, $uid, $type = 1, $mode = 'url')
    {
        if ($this->isRoot) {
            return true;//管理员允许访问任何页面
        }
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \muucmf\Auth();
        }
        if (!$Auth->check($rule, $uid, $type, $mode)) {
            return false;
        }
        return true;
    }

    /**
     * 记录请求
     */
    public function recordRequest(){
        $result = (new \app\admin\lib\Cloud())->recordRequest();
        return $result;
    }

    /**
     * 设置标题
     */
    public function setTitle(String $title = 'MuuCmf')
    {
        $this->title = $title;
        View::assign('title',$title);
    }

    /**
     * 获取版本号
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function version()
    {
        $path = PUBLIC_PATH . '/../data/version.ini';
        $version = file_get_contents($path);

        return $version;
    }

        /**
     * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
     *
     * @param string $table 模型名称,供M函数使用的参数
     * @param array  $data 修改的数据
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    final protected function editRow($table, $data, $where, $msg)
    {
        $id = array_unique((array)input('id/a', 0));
        $id = is_array($id) ? implode(',', $id) : $id;

        if($where) {
            $where = $where;
        }else{
            $where = ['id' => array('in', $id)];
        }

        $msg = array_merge(['success' => '操作成功！', 'error' => '操作失败', 'url' => ''], (array)$msg);
        
        if (Db::name($table)->where($where)->update($data) !== false) {
            //dump($this->success($msg['success'], '', $msg['url']));
            return $this->success($msg['success'], '', $msg['url']);
        } else {
            return $this->error($msg['error'], '', $msg['url']);
        }
    }

    /**
     * 禁用条目
     * @param string $table 模型名称
     * @param array  $where 查询时的 where()方法的参数
     * @param array  $msg 执行正确和错误的消息,可以设置四个元素 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    public function forbid($table, $where = [], $msg = ['success' => '状态禁用成功', 'error' => '状态禁用失败', 'url' => 'refresh'])
    {
        $data = ['status' => 0];
        return $this->editRow($table, $data, $where, $msg);
    }

    /**
     * 恢复条目
     * @param string $table 模型名称
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     *
     */
    public function resume($table, $where = [], $msg = ['success' => '启用成功', 'error' => '启用失败', 'url' => 'refresh'])
    {
        $data = ['status' => 1];
        return $this->editRow($table, $data, $where, $msg);
    }

    /**
     * 还原条目
     * @param string $table 模型名称
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    public function restore($table, $where = [], $msg = ['success' => '状态还原成功！', 'error' => '状态还原失败！', 'url' => 'refresh'])
    {
        $data = ['status' => 1];
        $where = array_merge(['status' => -1], $where);
        return $this->editRow($table, $data, $where, $msg);
    }

    /**
     * 条目假删除
     * @param string $table 模型名称
     * @param array  $where 查询时的where()方法的参数
     * @param array  $msg 执行正确和错误的消息 array('success'=>'','error'=>'', 'url'=>'','ajax'=>false)
     *                     url为跳转页面,ajax是否ajax方式(数字则为倒数计时秒数)
     */
    public function delete($table, $where = [], $msg =['success' => '删除成功', 'error' => '删除失败', 'url' => 'refresh'])
    {
        $data['status'] = -1;
        //$data['update_time'] = time();
        return $this->editRow($table, $data, $where, $msg);
    }

}
