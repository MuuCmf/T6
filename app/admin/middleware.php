<?php
// admin模块鉴权中间件
return [
    app\common\middleware\GlobleConfig::class,
    app\admin\middleware\Auth::class,
];
