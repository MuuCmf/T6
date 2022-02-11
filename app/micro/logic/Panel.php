<?php
// +----------------------------------------------------------------------
// | MuuCmf
// | panel通用面板功能数据处理
// +----------------------------------------------------------------------
namespace app\micro\logic;

use app\common\model\Module;

class Panel
{
    public function getList()
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
                $path = APP_PATH . $v['name'] . '/config/panel.php';
                $panel_arr = [];
                if(file_exists($path)){
                    $panel_arr = require($path);
                    $panel['app'][$v['name']] = $panel_arr;
                }
            }
        }

        return $panel;

    }
}
