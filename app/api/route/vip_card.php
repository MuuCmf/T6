<?php

use think\facade\Route;

Route::group('vip_card', function () {
    Route::get('lists', 'api/VipCard/lists');
});
