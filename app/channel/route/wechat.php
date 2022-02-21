<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: wechat.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/2/14
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
// 微信访问路由定义文件

use think\facade\Route;
//微信公众号
Route::group('official',function (){
    Route::rule('callback' ,'channel/api.WechatOfficialAccount/callback');
    Route::rule('oauthCallback' ,'channel/api.WechatOfficialAccount/oauthCallback');
    Route::rule('loginQrcode' ,'channel/api.WechatOfficialAccount/loginQrcode');
    Route::rule('hasScan' ,'channel/api.WechatOfficialAccount/hasScan');
    Route::rule('scanLogin' ,'channel/api.WechatOfficialAccount/scanLogin');
});