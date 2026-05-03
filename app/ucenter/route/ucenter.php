<?php

use think\facade\Route;

// ==================== ucenter 路由 ====================

// 地址管理
Route::group('address', function () {
    Route::get('list', 'ucenter/Address/list')->name('ucenter/Address/list');
    Route::get('edit', 'ucenter/Address/edit')->name('ucenter/Address/edit');
});

// 用户认证（登录、注册、找回密码）
Route::group('common', function () {
    Route::get('register', 'ucenter/Common/register')->name('ucenter/Common/register');
    Route::post('register', 'ucenter/Common/register')->name('ucenter/Common/register');
    Route::get('login', 'ucenter/Common/login')->name('ucenter/Common/login');
    Route::post('login', 'ucenter/Common/login')->name('ucenter/Common/login');
    Route::get('login', 'ucenter/Common/login')->name('ucenter/Common/login');
    Route::post('login', 'ucenter/Common/login')->name('ucenter/Common/login');
    Route::get('logout', 'ucenter/Common/logout')->name('ucenter/Common/logout');
    Route::get('mi', 'ucenter/Common/mi')->name('ucenter/Common/mi');
    Route::post('mi', 'ucenter/Common/mi')->name('ucenter/Common/mi');
    Route::get('agreement', 'ucenter/Common/agreement')->name('ucenter/Common/agreement');
});

// VIP会员
Route::group('vip', function () {
    Route::get('list', 'ucenter/Vip/list')->name('ucenter/Vip/list');
    Route::get('detail', 'ucenter/Vip/detail')->name('ucenter/Vip/detail');
    Route::get('create', 'ucenter/Vip/create')->name('ucenter/Vip/create');
});

// 积分
Route::group('score', function () {
    Route::get('index', 'ucenter/Score/index')->name('ucenter/Score/index');
    Route::get('rule', 'ucenter/Score/rule')->name('ucenter/Score/rule');
    Route::get('estate', 'ucenter/Score/estate')->name('ucenter/Score/estate');
});

// 订单
Route::group('orders', function () {
    Route::get('list', 'ucenter/Orders/list')->name('ucenter/Orders/list');
    Route::get('detail', 'ucenter/Orders/detail')->name('ucenter/Orders/detail');
});

// 消息
Route::group('message', function () {
    Route::get('modal', 'ucenter/Message/modal')->name('ucenter/Message/modal');
});

// 用户中心配置
Route::group('config', function () {
    Route::get('/', 'ucenter/Config/index')->name('ucenter/Config/index');
    Route::get('user_info', 'ucenter/Config/userInfo')->name('ucenter/Config/userInfo');
    Route::get('account', 'ucenter/Config/account')->name('ucenter/Config/account');
    Route::post('account', 'ucenter/Config/account')->name('ucenter/Config/account');
    Route::post('edit', 'ucenter/Config/edit')->name('ucenter/Config/edit');
    Route::get('password', 'ucenter/Config/password')->name('ucenter/Config/password');
    Route::post('password', 'ucenter/Config/password')->name('ucenter/Config/password');
    Route::get('wechat', 'ucenter/Config/wechat')->name('ucenter/Config/wechat');
    Route::post('wechat', 'ucenter/Config/wechat')->name('ucenter/Config/wechat');
    Route::post('unbind', 'ucenter/Config/unbind')->name('ucenter/Config/unbind');
    Route::get('authentication', 'ucenter/Config/authentication')->name('ucenter/Config/authentication');
    Route::post('authentication', 'ucenter/Config/authentication')->name('ucenter/Config/authentication');
});

// 创作者
Route::group('author', function () {
    Route::get('list', 'ucenter/Author/list')->name('ucenter/Author/list');
    Route::get('detail', 'ucenter/Author/detail')->name('ucenter/Author/detail');
});
