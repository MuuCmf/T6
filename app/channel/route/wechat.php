<?php
// 微信访问路由定义文件

use think\facade\Route;
//微信公众号
Route::group('official',function (){
    Route::rule('callback' ,'channel/api.WechatOfficialAccount/callback');
    Route::rule('oauthCallback' ,'channel/api.WechatOfficialAccount/oauthCallback');
    Route::rule('qrcode' ,'channel/api.WechatOfficialAccount/qrcode');
    Route::rule('hasScan' ,'channel/api.WechatOfficialAccount/hasScan');
    Route::rule('scanLogin' ,'channel/api.WechatOfficialAccount/scanLogin');
});

//抖音小程序
Route::group('douyin',function (){
    Route::rule('callback' ,'channel/api.DouyinMiniprogram/callback');
    Route::rule('qrcode' ,'channel/api.DouyinMiniprogram/qrcode');
});