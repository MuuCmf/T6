<?php

use think\facade\Route;

Route::group('wechat_mp', function () {
    Route::get('code', 'WechatMiniProgram/code');
    Route::post('login', 'WechatMiniProgram/login');
    Route::post('bindMobile', 'WechatMiniProgram/bindMobile');
});
