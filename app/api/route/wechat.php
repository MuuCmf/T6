<?php

use think\facade\Route;

Route::group('wechat', function () {
    // 公众号配置
    Route::get('config', 'WechatOfficialAccount/config');
    Route::any('callback', 'WechatOfficialAccount/callback');
    Route::get('qrcode', 'WechatOfficialAccount/qrcode');
    Route::post('scan_login', 'WechatOfficialAccount/scanLogin');
    Route::get('has_scan', 'WechatOfficialAccount/hasScan');
    Route::get('oauth', 'WechatOfficialAccount/oauth');
    Route::get('oauth_callback', 'WechatOfficialAccount/oauthCallback');
    Route::post('jssdk', 'WechatOfficialAccount/jssdk');
});
