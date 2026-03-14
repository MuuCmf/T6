<?php

use think\facade\Route;

Route::group('wechat_work', function () {
    Route::get('config', 'WechatWork/config');
    Route::any('callback', 'WechatWork/callback');
});
