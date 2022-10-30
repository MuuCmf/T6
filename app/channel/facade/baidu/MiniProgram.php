<?php
namespace app\channel\facade\baidu;

use think\Facade;

/**
 * Class MiniProgram
 */
class MiniProgram extends Facade {

    // getFacadeClass: 获取当前Facade对应类名
    protected static function getFacadeClass()
    {
        // 返回当前类代理的类
        return 'app\channel\service\baidu\BaiduMp';
    }
}