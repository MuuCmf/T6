<?php
use think\facade\Route;

// 文章
Route::rule('index', 'articles/pc.Index/index');
Route::rule('lists', 'articles/pc.Index/lists');
Route::rule('detail', 'articles/pc.Index/detail');





