<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: OfficialAccount.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/28
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\service\wechat\facade;

use think\Facade;

/**
 * Class OfficialAccount
 * @method serverOAath() static
 * @method getWechatServerIps() static
 * @method getWechatServerIp() static
 * @method getMenu() static
 * @method currentMenu() static
 * @method createMenu() static
 * @method currentMessage() static
 * @method getMaterialList($type,$offset,$count) static
 * @method getMaterial($media_id) static
 */
class OfficialAccount extends Facade {
    // getFacadeClass: 获取当前Facade对应类名
    protected static function getFacadeClass()
    {
        // 返回当前类代理的类
        return 'app\common\service\wechat\OfficialAccount';
    }
}