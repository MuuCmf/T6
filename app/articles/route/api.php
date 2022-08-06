<?php
use think\facade\Route;

// 文章
Route::rule('api/articles/lists', 'api.articles/lists');
Route::rule('api/articles/detail', 'api.articles/detail');

// 评论
Route::rule('api/comment/lists', 'api.comment/lists');
Route::rule('api/comment/add', 'api.comment/add')->middleware(\app\common\middleware\CheckAuth::class);
Route::rule('api/comment/support', 'api.comment/support')->middleware(\app\common\middleware\CheckAuth::class);


