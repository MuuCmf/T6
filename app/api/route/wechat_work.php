<?php

use think\facade\Route;

Route::group('wechat_work', function () {
    Route::get('config', 'api/WechatWork/config');
    Route::any('callback', 'api/WechatWork/callback');
});
