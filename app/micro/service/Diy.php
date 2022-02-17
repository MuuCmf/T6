<?php
// +----------------------------------------------------------------------
// | 微页应用 MuuCmf_micro V1.0.0
// | 多应用Diy组件数据处理
// | TODO: 在多应用目录下创建micro目录
// +----------------------------------------------------------------------
namespace app\micro\service;

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
}
