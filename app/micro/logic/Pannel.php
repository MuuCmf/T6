<?php
namespace app\micro\logic;

use app\common\model\Module;

class Pannel
{
    public function getList()
    {
        // 初始化空数据
        $pannel = [];
        $system_pannel = config('pannel');
        $pannel['system'] = $system_pannel;
        $pannel['app'] = [];
        // 获取应用面板按钮
        $module_list = (new Module)->getAll([['is_setup', '=', 1]]);
        foreach($module_list as $v){
            $path = APP_PATH . $v['name'] . '/config/pannel.php';
            $pannel_arr = [];
            if(file_exists($path)){
                $pannel_arr = require($path);
                $pannel['app'][$v['name']] = $pannel_arr;
            }
            //dump($pannel_arr);
        }

        return $pannel;

    }
}
