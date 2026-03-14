<?php

use think\facade\Route;

Route::group('douyin', function () {
    Route::any('callback', 'DouyinMiniProgram/callback');
    Route::get('code', 'DouyinMiniProgram/code');
    Route::post('login', 'DouyinMiniProgram/login');
    Route::get('create_qrcode', 'DouyinMiniProgram/createQrcode');
    Route::post('bind_mobile', 'DouyinMiniProgram/bindMobile');
});
