<?php

namespace app\common\logic;

use app\common\model\Module;

class Favorites extends Base
{
    public function formatData($data)
    {
        // 约定各应用内容交由应用内部处理
        // 约定类名 Favorites 约定方法formatData
        $class_namespace = "\\app\\{$data['app']}\\logic\\Favorites";
        if (class_exists($class_namespace)) {
            $appLogic = new $class_namespace;
            $data = $appLogic->formatData($data);
        }

        if (empty($data['products'])) {
            $data['metadata'] = $data['products'] = json_decode($data['metadata'], true);
            $data['products'] = $this->setImgAttr($data['products'], '1:1');
            if (isset($data['products']['price'])) {
                $data['products']['price'] = sprintf("%.2f", $data['products']['price'] / 100);
            }
        }

        //获取应用名
        $data['module_name'] =  $data['app'] == 'system' ? '系统' : Module::where('name', $data['app'])->value('alias');
        $data['user_info'] = query_user($data['uid'], ['nickname', 'avatar']); //用户信息

        $data = $this->setTimeAttr($data);

        return $data;
    }
}
