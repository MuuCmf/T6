<?php
// 全局中间件
return [
    \think\middleware\SessionInit::class,
    \app\common\middleware\DbConfig::class,
];