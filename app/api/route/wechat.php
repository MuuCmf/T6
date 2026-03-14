<?php

use think\facade\Route;

Route::group('wechat', function () {
    // 公众号配置
    Route::get('config', 'api/WechatOfficialAccount/config');
    Route::any('callback', 'api/WechatOfficialAccount/callback');
    Route::get('qrcode', 'api/WechatOfficialAccount/qrcode');
    Route::post('scan_login', 'api/WechatOfficialAccount/scanLogin');
    Route::get('has_scan', 'api/WechatOfficialAccount/hasScan');
    Route::get('oauth', 'api/WechatOfficialAccount/oauth');
    Route::get('oauth_callback', 'api/WechatOfficialAccount/oauthCallback');
    Route::post('jssdk', 'api/WechatOfficialAccount/jssdk');
});
