<?php

use think\facade\Route;

// 文章管理路由
Route::group('admin', function () {

    // 配置页面
    Route::rule('config/index', 'articles/admin.Config/index');

    // 文章列表
    Route::any('articles/lists', 'articles/admin.Articles/lists');
    // 文章新增/编辑
    Route::any('articles/edit', 'articles/admin.Articles/edit');
    // 文章状态设置
    Route::post('articles/status', 'articles/admin.Articles/status');
    // 文章审核
    Route::post('articles/verify', 'articles/admin.Articles/verify');

    // 分类列表
    Route::rule('category/lists', 'articles/admin.Category/lists');
    // 分类新增/编辑
    Route::rule('category/edit', 'articles/admin.Category/edit');
    // 分类状态设置
    Route::rule('category/status', 'articles/admin.Category/status');

    // 评论列表
    Route::rule('comment/lists', 'articles/admin.Comment/lists');
    // 评论新增/编辑
    Route::rule('comment/edit', 'articles/admin.Comment/edit');
    // 评论状态设置
    Route::rule('comment/status', 'articles/admin.Comment/status');
});





