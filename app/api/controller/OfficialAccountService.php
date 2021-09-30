<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: OfficialAccountService.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/29
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\service\wechat\facade\OfficialAccount;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;

class OfficialAccountService{
    public function callback()
    {
        $app = OfficialAccount::getApp();


        //消息通知
        $app->server->push(function ($message) {
//            switch ($message['MsgType']){
//                case 'text':
//
//                    break;
//            }
            return new Image('Fi_ULDUOd0HAYf6xALb46lYFxejrw-xT8M9G8PlNx1I');
            return new Media('Fi_ULDUOd0HAYf6xALb46lYFxejrw-xT8M9G8PlNx1I');
        });
        $app->server->serve();


        //token 回调
        OfficialAccount::serverOAath();
    }
    public function autoReply(){
        $app = OfficialAccount::getApp();
        $list = $app->material->list('image', 0, 10);

        dump($list);
    }
}