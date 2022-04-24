<?php
namespace app\channel\facade\channel;

use think\Facade;

/**
 * Class Channel
 * @method init(string $appid ,string $channel,int $shopid) static
 */
class Pay extends Facade {

    // getFacadeClass: 获取当前Facade对应类名
    protected static function getFacadeClass()
    {
        // 返回当前类代理的类
        return 'app\channel\service\channel\Pay';
    }
}