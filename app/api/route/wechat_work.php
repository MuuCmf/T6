<?php

use think\facade\Route;

Route::group('wechat_work', function () {
    Route::get('config', 'api/WechatWork/config');
    Route::any('callback', 'api/WechatWork/callback');
    Route::get('oauth', 'api/WechatWork/oauth');
    Route::post('jssdk', 'api/WechatWork/jssdk');
    Route::any('oauth_callback', 'api/WechatWork/oauthCallback');
});
