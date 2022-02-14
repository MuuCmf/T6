<?php
// +----------------------------------------------------------------------
// | 微页应用 MuuCmf_micro V1.0.0
// | 多应用Diy组件数据处理
// | TODO: 在多应用目录下创建micro目录
// +----------------------------------------------------------------------
namespace app\micro\service;

use app\common\model\Module;

class Diy
{
    /**
     * diy组件配置数据获取
     */
    public function getConfig()
    {
        // 初始化空数据
        $panel = [];
        // 获取公共部分
        $panel['common'] = config('panel');

        // 获取应用部分
        $panel['app'] = [];
        $module_list = (new Module)->getAll([['is_setup', '=', 1]]);
        foreach($module_list as $v){
            if($v['name'] != 'micro'){
                // 获取约定的目录是否存在
                $dir = APP_PATH . $v['name'] . '/' . 'micro';
                if (is_dir($dir)) {
                    $panel['app'][$v['name']]['name'] = $v['name'];
                    $panel['app'][$v['name']]['alias'] = $v['alias'];
                    //打开
                    if($dh = @opendir($dir)){
                        //读取
                        while(($file = readdir($dh)) !== false){
                            if($file != '.' && $file != '..'){
                                // 去除文件名后缀
                                $name = $v['name'] . '\\' . str_replace(".php","",$file);
                                $class = 'app\\' . $v['name'] . '\\micro\\' . str_replace(".php","",$file);
                                // 绑定到容器
                                bind($name, $class);
                                // 获取组件唯一标识
                                $type = app($name)->_type;
                                $panel['app'][$v['name']]['list'][$type]['app'] = $v['name'];
                                $panel['app'][$v['name']]['list'][$type]['app_name'] = $v['alias'];
                                $panel['app'][$v['name']]['list'][$type]['title'] = app($name)->_title;
                                $panel['app'][$v['name']]['list'][$type]['type'] = app($name)->_type;
                                $panel['app'][$v['name']]['list'][$type]['icon'] = app($name)->_icon;
                                $panel['app'][$v['name']]['list'][$type]['api'] = app($name)->_api;
                                $panel['app'][$v['name']]['list'][$type]['tmpl'] = app($name)->_template;
                            }
                        }
                        //关闭
                        closedir($dh);
                    }
                }
            }
        }
        
        return $panel;

    }
}
