<?php
namespace app\channel\facade\wechat;

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
        return 'app\channel\service\wechat\MiniProgram';
    }
}