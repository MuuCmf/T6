<?php
namespace app\channel\facade\wechat;

use think\Facade;

class Work extends Facade {
    // getFacadeClass: 获取当前Facade对应类名
    protected static function getFacadeClass()
    {
        // 返回当前类代理的类
        return 'app\channel\service\wechat\Work';
    }
}