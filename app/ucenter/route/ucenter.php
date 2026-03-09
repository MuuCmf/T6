<?php

use think\facade\Route;

// ==================== ucenter 路由 ====================

// 地址管理
Route::group('address', function () {
    Route::get('list', 'Address/list')->name('ucenter/Address/list');
    Route::get('edit', 'Address/edit')->name('ucenter/Address/edit');
});

// 用户认证（登录、注册、找回密码）
Route::group('common', function () {
    Route::get('register', 'Common/register')->name('ucenter/Common/register');
    Route::post('register', 'Common/register')->name('ucenter/Common/register');
    Route::get('login', 'Common/login')->name('ucenter/Common/login');
    Route::post('login', 'Common/login')->name('ucenter/Common/login');
    Route::get('login', 'Common/login')->name('ucenter/Common/login');
    Route::post('login', 'Common/login')->name('ucenter/Common/login');
    Route::get('logout', 'Common/logout')->name('ucenter/Common/logout');
    Route::get('mi', 'Common/mi')->name('ucenter/Common/mi');
    Route::post('mi', 'Common/mi')->name('ucenter/Common/mi');
    Route::get('agreement', 'Common/agreement')->name('ucenter/Common/agreement');
});

// VIP会员
Route::group('vip', function () {
    Route::get('list', 'Vip/list')->name('ucenter/Vip/list');
    Route::get('detail', 'Vip/detail')->name('ucenter/Vip/detail');
    Route::get('create', 'Vip/create')->name('ucenter/Vip/create');
});

// 积分
Route::group('score', function () {
    Route::get('index', 'Score/index')->name('ucenter/Score/index');
    Route::get('rule', 'Score/rule')->name('ucenter/Score/rule');
    Route::get('estate', 'Score/estate')->name('ucenter/Score/estate');
});

// 订单
Route::group('orders', function () {
    Route::get('list', 'Orders/list')->name('ucenter/Orders/list');
    Route::get('detail', 'Orders/detail')->name('ucenter/Orders/detail');
});

// 消息
Route::group('message', function () {
    Route::get('modal', 'Message/modal')->name('ucenter/Message/modal');
});

// 用户中心配置
Route::group('config', function () {
    Route::get('index', 'Config/index')->name('ucenter/Config/index');
    Route::get('user_info', 'Config/userInfo')->name('ucenter/Config/userInfo');
    Route::get('account', 'Config/account')->name('ucenter/Config/account');
    Route::post('account', 'Config/account')->name('ucenter/Config/account');
    Route::post('edit', 'Config/edit')->name('ucenter/Config/edit');
    Route::get('password', 'Config/password')->name('ucenter/Config/password');
    Route::post('password', 'Config/password')->name('ucenter/Config/password');
    Route::get('wechat', 'Config/wechat')->name('ucenter/Config/wechat');
    Route::post('wechat', 'Config/wechat')->name('ucenter/Config/wechat');
    Route::post('unbind', 'Config/unbind')->name('ucenter/Config/unbind');
    Route::get('authentication', 'Config/authentication')->name('ucenter/Config/authentication');
    Route::post('authentication', 'Config/authentication')->name('ucenter/Config/authentication');
});

// 创作者
Route::group('author', function () {
    Route::get('list', 'Author/list')->name('ucenter/Author/list');
    Route::get('detail', 'Author/detail')->name('ucenter/Author/detail');
});
