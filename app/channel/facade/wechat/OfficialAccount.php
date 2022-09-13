<?php
namespace app\channel\facade\wechat;

use think\Facade;

/**
 * Class OfficialAccount
 * @method serverOAath() static
 * @method getWechatServerIps() static
 * @method getWechatServerIp() static
 * @method getMenu() static
 * @method getApp() static
 * @method currentMenu() static
 * @method createMenu() static
 * @method currentMessage() static
 * @method getMaterialList($type,$offset,$count) static
 * @method getMaterial($media_id) static
 * @method getToken() static
 * @method oauth(array $params) static
 * @method createQrcode(string $content ,$expiration_time) static
 * @method getQrcodeUrl(string $ticket) static
 * @method sendTemplateMsg(array $data) static
 */
class OfficialAccount extends Facade {
    // getFacadeClass: 获取当前Facade对应类名
    protected static function getFacadeClass()
    {
        // 返回当前类代理的类
        return 'app\channel\service\wechat\OfficialAccount';
    }
}