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
namespace app\unions\facade\wechat;

use think\Facade;

/**
 * Class OfficialAccount
 * @method user(string $code) static
 * @method decryptData(string $code ,string $iv ,string $encryptedData) static
 * @method unlimitQrcode(string $scene ,array $optional) static
 * @method sendTemplateMsg(array $data) static
 * @method getLiveRooms() static
 * @method getLivePlaybacks(int $roomid) static
 */
class MiniProgram extends Facade {

    // getFacadeClass: 获取当前Facade对应类名
    protected static function getFacadeClass()
    {
        // 返回当前类代理的类
        return 'app\unions\service\wechat\MiniProgram';
    }
}