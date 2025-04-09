<?php
// 路由定义文件

use think\facade\Route;
//微信公众号
Route::group('official',function (){
    Route::rule('config' ,'channel/api.WechatOfficialAccount/config');
    Route::rule('callback' ,'channel/api.WechatOfficialAccount/callback');
    Route::rule('oauth' ,'channel/api.WechatOfficialAccount/oauth');
    Route::rule('oauthCallback' ,'channel/api.WechatOfficialAccount/oauthCallback');
    Route::rule('qrcode' ,'channel/api.WechatOfficialAccount/qrcode');
    Route::rule('hasScan' ,'channel/api.WechatOfficialAccount/hasScan');
    Route::rule('scanLogin' ,'channel/api.WechatOfficialAccount/scanLogin');
});

//微信小程序
Route::group('weixin_mp',function (){
    Route::rule('code' ,'channel/api.WechatMiniProgram/code');
    Route::rule('login' ,'channel/api.WechatMiniProgram/login');
    Route::rule('unlimitQrcode' ,'channel/api.WechatMiniProgram/unlimitQrcode');
    Route::rule('bindMobile' ,'channel/api.WechatMiniProgram/bindMobile');
    Route::rule('to_mp_lists' ,'channel/api.WechatMiniProgram/toMiniProgramLists');
    Route::rule('to_mp_detail' ,'channel/api.WechatMiniProgram/toMiniProgramDetail');
});

//企业微信
Route::group('work',function (){
    Route::rule('config' ,'channel/api.WechatWork/config');
    Route::rule('callback' ,'channel/api.WechatWork/callback');
    Route::rule('oauth' ,'channel/api.WechatWork/oauth');
    Route::rule('oauthCallback' ,'channel/api.WechatWork/oauthCallback');
});

//抖音小程序
Route::group('douyin',function (){
    Route::rule('callback' ,'channel/api.DouyinMiniProgram/callback');
    Route::rule('qrcode' ,'channel/api.DouyinMiniProgram/createQrcode');
});

//百度小程序
Route::group('baidu',function (){
    Route::rule('callback' ,'channel/api.BaiduMiniProgram/callback');
    Route::rule('qrcode' ,'channel/api.BaiduMiniProgram/createQrcode');
});