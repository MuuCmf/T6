<?php

use think\facade\Route;

Route::group('wechat_mp', function () {
    Route::get('code', 'api/WechatMiniProgram/code');
    Route::post('login', 'api/WechatMiniProgram/login');
    Route::post('bindMobile', 'api/WechatMiniProgram/bindMobile');
});
