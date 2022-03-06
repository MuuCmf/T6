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

        // 数组排序更改，将micro模块放置在第一位
        $micro_arr = [];
        foreach($module_list as $v){
            if($v['name'] == 'micro'){
                $micro_arr[] = $v;
            }
        }
        $module_list = array_unique(array_merge($micro_arr, $module_list), SORT_REGULAR);
        foreach($module_list as $v){
            // 获取约定的目录是否存在
            $dir = APP_PATH . $v['name'] . '/service/' . 'diy';
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
                            $class = 'app\\' . $v['name'] . '\\service\\diy\\' . str_replace(".php","",$file);
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
        if (!empty($page_data['data'])){
            // 处理数据块数据模板
            foreach($page_data['data'] as $key => $vo){
                if(array_key_exists($vo['type'], $diy_params_config[$vo['app']]['list'])){
                    $tmpl = $diy_params_config[$vo['app']]['list'][$vo['type']]['tmpl']['view'];
                    $view_tmpl .= View::fetch($tmpl, [
                        'key' => $key,
                        'data' => $vo
                    ]);
                }
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
        $style = '';
        $script = '';
        foreach($diy_params_config as $v){
            foreach($v['list'] as $c_v){
                if(!empty($c_v['static']['mobile']['css']) && file_exists($c_v['static']['mobile']['css'])){

                    $style .= file_get_contents($c_v['static']['mobile']['css']);
                }
                if(!empty($c_v['static']['mobile']['js']) && file_exists($c_v['static']['mobile']['js'])){
                    $script .= file_get_contents($c_v['static']['mobile']['js']);
                }
            }
        }
        $style='<style>' .$style. '</style>';
        $script = '<script>' .$script. '</script>';
        $app_static_tmpl = $style . $script;
        
        return $app_static_tmpl;
    }

    // /**
    //  * 获取DIY组件静态资源模板内容
    //  */
    // public function getStaticTmpl()
    // {
    //     $diy_params_config = $this->getConfig();
    //     $app_static_tmpl = '';
    //     foreach($diy_params_config as $k=>$v){
    //         foreach($v['list'] as $c_k=>$c_v){
    //             if(!empty($c_v['static']['mobile']['css'])){
    //                 $app_static_tmpl .= '<link href="'.$c_v['static']['mobile']['css'].'" rel="stylesheet" type="text/css"/>';
    //             }
    //             if(!empty($c_v['static']['mobile']['js'])){
    //                 $app_static_tmpl .= '<script type="text/javascript" src="'.$c_v['static']['mobile']['js'].'"></script>';
    //             }
    //         }
    //     }

    //     return $app_static_tmpl;
    // }

    /**
     * 各应用在自定义页的数据处理
     */
    public function handle($data, $shopid)
    {
        // 应用
        
        // 绑定到容器
        $name = $data['app'] . '\\' . Str::studly($data['type']);
        $class = 'app\\' . $data['app'] . '\\service\\diy\\' . Str::studly($data['type']);
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
     * 获取系统图标列表
     *
     * @return     array  The icon lists.
     */
    public function getIconLists()
    {
        //获取系统图标
        //取得系统图标所在目录
        $dir  =  PUBLIC_PATH . '/static/micro/images/icon';
        //初始化空数组
        $file_arr = array();
        //判断目标目录是否是文件夹
        if(is_dir($dir)){
            //打开
            if($dh = @opendir($dir)){
                //读取
                while(($file = readdir($dh)) !== false){

                    if($file != '.' && $file != '..'){

                        $file_arr[] = $file;
                    }
                }
                //关闭
                closedir($dh);
            }
        }

        $icon_arr = array();
        foreach($file_arr as $val){
            $icon_dir = $dir .'/'.$val;

            if($dh = @opendir($icon_dir)){
                //读取
                while(($file = readdir($dh)) !== false){

                    if($file != '.' && $file != '..'){

                        $icon_arr_item = array(
                            'title' => $file,
                            'url' => request()->domain() . '/static/micro/images/icon/' . $val .'/'. $file,
                        );
                        $icon_arr[$val][] = $icon_arr_item;
                    }
                }
                //关闭
                closedir($dh);
            }

        }

        return $icon_arr;
    }

    
}
