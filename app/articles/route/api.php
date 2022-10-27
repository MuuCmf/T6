<?php
use think\facade\Route;

// 文章
Route::rule('api/articles/lists', 'articles/api.articles/lists');
Route::rule('api/articles/detail', 'articles/api.articles/detail');

// 评论
Route::rule('api/comment/lists', 'articles/api.comment/lists');
Route::rule('api/comment/add', 'articles/api.comment/add')->middleware(\app\common\middleware\CheckAuth::class);
Route::rule('api/comment/support', 'articles/api.comment/support')->middleware(\app\common\middleware\CheckAuth::class);

// 点赞
Route::rule('api/support/action', 'articles/api.support/action');


