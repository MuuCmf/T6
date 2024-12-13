<?php

namespace app\common\logic;

use app\common\model\Module;

class History extends Base
{
    public function formatData($data)
    {
        // 约定各应用内容交由应用内部处理
        // 约定类名 History 约定方法formatData
        $class_namespace = "\\app\\{$data['app']}\\logic\\History";
        if (class_exists($class_namespace)) {
            $appLogic = new $class_namespace;
            $data = $appLogic->formatData($data);
        }

        if(empty($data['products'])){
            $data['metadata'] = $data['products'] = json_decode($data['metadata'], true);
            $data['products'] = $this->setImgAttr($data['products'], '1:1');
            if (isset($data['products']['price'])) {
                $data['products']['price'] = sprintf("%.2f", $data['products']['price'] / 100);
            }
        }

        $data['info_id'] = (string)$data['info_id'];

        //获取应用名
        $data['module_name'] =  $data['app'] == 'system' ? '系统' : Module::where('name', $data['app'])->value('alias');
        // 获取应用信息
        $data['app_info'] = (new Module())->getModule($data['app']);
        // 获取用户信息
        $data['user_info'] = query_user($data['uid'], ['nickname', 'avatar']); //用户信息

        if (!empty($data['create_time'])) {
            $data['create_time_str'] = time_format($data['create_time']);
            $data['create_time_friendly_str'] = friendly_date($data['create_time']);
        }
        if (!empty($data['update_time'])) {
            $data['update_time_str'] = time_format($data['update_time']);
            $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        }
        
        return $data;
    }
}
