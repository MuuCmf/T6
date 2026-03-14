<?php

use think\facade\Route;

Route::group('wechat_mp', function () {
    Route::get('config', 'api/WechatMiniProgram/config');
    Route::get('code', 'api/WechatMiniProgram/code');
    Route::post('login', 'api/WechatMiniProgram/login');
    Route::get('unlimitQrcode', 'api/WechatMiniProgram/unlimitQrcode');
    Route::post('bindMobile', 'api/WechatMiniProgram/bindMobile');
    Route::get('toMiniProgramLists', 'api/WechatMiniProgram/toMiniProgramLists');
    Route::get('toMiniProgramDetail', 'api/WechatMiniProgram/toMiniProgramDetail');
});
