<?php

use think\facade\Route;

Route::group('member', function () {
    // 注册
    Route::post('register', 'api/Member/register');
    // 登录
    Route::post('login', 'api/Member/login');
    // 密码找回
    Route::post('mi', 'api/Member/mi');
    // 用户服务协议
    Route::get('agreement', 'api/Member/agreement');
    // 用户隐私条款
    Route::get('privacy', 'api/Member/privacy');
    // 退出登录
    Route::post('logout', 'api/Member/logout');
    // 用户注销
    Route::post('logoff', 'api/Member/logoff');
    // 获取用户信息
    Route::get('user_info', 'api/Member/userInfo');
    // 绑定用户手机或邮箱
    Route::post('bind', 'api/Member/bind');
    // 修改用户信息
    Route::post('edit', 'api/Member/edit');
    // 修改密码
    Route::post('password', 'api/Member/password');
    // 保存头像
    Route::post('avatar', 'api/Member/avatar');
    // 绑定微信账号
    Route::post('wechat', 'api/Member/wechat');
    // 解除微信用户绑定
    Route::post('unbind', 'api/Member/unbind');
});
