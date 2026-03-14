<?php

use think\facade\Route;

Route::group('douyin', function () {
    Route::any('callback', 'api/DouyinMiniProgram/callback');
    Route::get('code', 'api/DouyinMiniProgram/code');
    Route::post('login', 'api/DouyinMiniProgram/login');
    Route::get('create_qrcode', 'api/DouyinMiniProgram/createQrcode');
    Route::post('bind_mobile', 'api/DouyinMiniProgram/bindMobile');
});
