<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

//模块目录
define('APP_PATH', __DIR__ . '/../app/');
//public目录
define('PUBLIC_PATH', __DIR__);
//static目录
define('STATIC_URL', __DIR__ . '/static');
// 判断是否安装MuuCmf
if (!is_file(__DIR__ . '/../data/install.lock'))
{	
    header("location: /install.php");
    exit;
}

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
