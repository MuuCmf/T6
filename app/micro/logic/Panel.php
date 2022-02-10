<?php
namespace app\micro\logic;

use app\common\model\Module;

class Panel
{
    public function getList()
    {
        // 初始化空数据
        $panel = [];
        $system_panel = config('panel');
        $panel['system'] = $system_panel;
        $panel['app'] = [];
        // 获取应用面板按钮
        $module_list = (new Module)->getAll([['is_setup', '=', 1]]);
        foreach($module_list as $v){
            $path = APP_PATH . $v['name'] . '/config/panel.php';
            $panel_arr = [];
            if(file_exists($path)){
                $panel_arr = require($path);
                $panel['app'][$v['name']] = $panel_arr;
            }
        }

        return $panel;

    }
}
