<?php
// +----------------------------------------------------------------------
// | 微页应用 MuuCmf_micro V1.0.0
// | 多应用Diy组件数据处理
// | TODO: 在多应用目录下创建diy目录
// +----------------------------------------------------------------------
namespace app\micro\service;

use think\facade\View;
use think\helper\Str;
use app\common\model\Module;

class Diy
{
    /**
     * diy组件配置数据获取
     */
    public function getConfig()
    {
        // 获取应用部分
        $config = [];
        $module_list = (new Module)->getAll([['is_setup', '=', 1]]);
        foreach($module_list as $v){
            // 获取约定的目录是否存在
            $dir = APP_PATH . $v['name'] . '/' . 'diy';
            if (is_dir($dir)) {
                $config[$v['name']]['name'] = $v['name'];
                $config[$v['name']]['alias'] = $v['alias'];
                //打开
                if($dh = @opendir($dir)){
                    //读取
                    while(($file = readdir($dh)) !== false){
                        if($file != '.' && $file != '..'){
                            // 去除文件名后缀
                            $name = $v['name'] . '\\' . str_replace(".php","",$file);
                            $class = 'app\\' . $v['name'] . '\\diy\\' . str_replace(".php","",$file);
                            // 绑定到容器
                            bind($name, $class);
                            // 获取组件唯一标识
                            $type = app($name)->_type;
                            $config[$v['name']]['list'][$type]['app'] = $v['name'];
                            $config[$v['name']]['list'][$type]['app_name'] = $v['alias'];
                            $config[$v['name']]['list'][$type]['title'] = app($name)->_title;
                            $config[$v['name']]['list'][$type]['type'] = app($name)->_type;
                            $config[$v['name']]['list'][$type]['icon'] = app($name)->_icon;
                            
                            $config[$v['name']]['list'][$type]['api'] = app($name)->_api;
                            $config[$v['name']]['list'][$type]['tmpl'] = app($name)->_template;
                            if(isset(app($name)->_static)) {
                                $config[$v['name']]['list'][$type]['static'] = app($name)->_static;
                            }
                            
                        }
                    }
                    //关闭
                    closedir($dh);
                }
            }
        }
        
        return $config;

    }

    /**
     * 获取DIY组件控制和显示的模板
     */
    public function getViewTmpl($page_data = [])
    {
        $diy_params_config = $this->getConfig();
        $view_tmpl = '';
        if (!empty($page_data)){
            // 处理数据块数据模板
            foreach($page_data['data'] as $key => $vo){
                $tmpl = $diy_params_config[$vo['app']]['list'][$vo['type']]['tmpl']['view'];
                $view_tmpl .= View::fetch($tmpl, [
                    'key' => $key,
                    'data' => $vo
                ]);
            }
        }

        return $view_tmpl;
    }

    /**
     * 获取DIY组件初始内容
     */
    public function getScriptTmpl()
    {
        $diy_params_config = $this->getConfig();
        $app_script_tmpl = '';
        foreach($diy_params_config as $k=>$v){
            foreach($v['list'] as $c_k=>$c_v){
                if(file_exists($c_v['tmpl']['script'])){
                    $app_script_tmpl .= View::fetch($c_v['tmpl']['script']);
                }
            }
        }

        return $app_script_tmpl;
    }

    /**
     * 获取DIY组件静态资源模板内容
     */
    public function getStaticTmpl()
    {
        $diy_params_config = $this->getConfig();
        $app_static_tmpl = '';
        foreach($diy_params_config as $k=>$v){
            foreach($v['list'] as $c_k=>$c_v){
                if(!empty($c_v['static']['mobile']['css'])){
                    $app_static_tmpl .= '<link href="'.$c_v['static']['mobile']['css'].'" rel="stylesheet" type="text/css"/>';
                }
                if(!empty($c_v['static']['mobile']['js'])){
                    $app_static_tmpl .= '<script type="text/javascript" src="'.$c_v['static']['mobile']['js'].'"></script>';
                }
            }
        }

        return $app_static_tmpl;
    }

    /**
     * 各应用在自定义页的数据处理
     */
    public function handle($data, $shopid)
    {
        // 应用
        
        // 绑定到容器
        $name = $data['app'] . '\\' . Str::studly($data['type']);
        $class = 'app\\' . $data['app'] . '\\diy\\' . Str::studly($data['type']);
        bind($name, $class);
        try {
            // 约定数据处理方法为 handle
            $data = app($name)->handle($data, $shopid);
        } catch (\Exception $e) {
            return $data;
            //throw new \think\Exception('异常消息', 10006);
        }
        
        return $data;
    }

    /**
     * 连接至参数配置
     */
    public function links()
    {
        return [
            [
                'icon' => 'fa-desktop',
                'sys_type' => 'detail',
                'link_type' => 'micro_page',
                'link_type_title' => '自定义页面',
                'api' => url('micro/admin.page/api')
            ],[
                'icon' => 'fa-bars',
                'sys_type' => 'list',
                'link_type' => 'knowledge_list',
                'link_type_title' => '点播课列表',
                'api' => url('classroom/admin.knowledge/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-file-text-o',
                'sys_type' => 'detail',
                'link_type' => 'knowledge_detail',
                'link_type_title' => '点播课详情',
                'api' => url('classroom/admin.knowledge/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-indent',
                'sys_type' => 'list',
                'link_type' => 'column_list',
                'link_type_title' => '专栏列表',
                'app' => 'classroom',
                'controller' => 'column',
                'action' => 'lists',
                'api' => url('classroom/admin.column/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-newspaper-o',
                'sys_type' => 'detail',
                'link_type' => 'column_detail',
                'link_type_title' => '专栏详情',
                'app' => 'classroom',
                'controller' => 'column',
                'action' => 'lists',
                'api' => url('classroom/admin.column/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-indent',
                'sys_type' => 'list',
                'link_type' => 'offline_list',
                'link_type_title' => '线下课列表',
                'api' => url('classroom/admin.offline/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-map-marker',
                'sys_type' => 'detail',
                'link_type' => 'offline_detail',
                'link_type_title' => '线下课详情',
                'api' => url('classroom/admin.column/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-indent',
                'sys_type' => 'list',
                'link_type' => 'material_list',
                'link_type_title' => '资料列表',
                'api' => url('classroom/admin.material/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-download',
                'sys_type' => 'detail',
                'link_type' => 'material_detail',
                'link_type_title' => '资料详情',
                'api' => url('classroom/admin.material/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-indent',
                'sys_type' => 'list',
                'link_type' => 'exam_paper_list',
                'link_type_title' => '试卷列表',
                'api' => url('exam/admin.paper/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-newspaper-o',
                'sys_type' => 'detail',
                'link_type' => 'exam_paper_detail',
                'link_type_title' => '试卷详情',
                'api' => url('exam/admin.paper/lists'),
                'category_api' => url('classroom/admin.category/tree'),
            ],[
                'icon' => 'fa-indent',
                'sys_type' => 'direct',
                'link_type' => 'category',
                'link_type_title' => '分类页',
                'api' => url('exam/admin.category/lists')
            ],[
                'icon' => 'fa-user',
                'sys_type' => 'direct',
                'link_type' => 'member',
                'link_type_title' => '会员服务',
                'api' => url('exam/admin.vip/lists')
            ]
        ];
    }

    /**
     * 链接至参数处理
     */
    public function linkParams(){

        $links = $this->links();

        // if($_GPC['action'] == 'pc_diy' || $_GPC['action'] == 'pc_head' || $_GPC['action'] == 'pc_foot'){
        //     // pc端
        //     $port = 'webapp';
        //     unset($link[5]); //删除分类页
        //     unset($link[6]); //删除会员页
        // }

        // foreach($link as $k=>&$v){
            
        // }
        // unset($v);
        return $links;
    }

    public function linkToUrl($linkParam = [], $channel = 'mobile'){
        //初始化返回值
        $result = '';

        return $result;
    }
}
