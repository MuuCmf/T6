<?php

return[
    // 权限设置
    'auth_on'            => true,                                             // 认证开关
    'auth_type'          => 1,                                                // 认证方式，1为实时认证；2为登录认证。
    'auth_group'         => 'muucmf_auth_group',                              // 用户组数据表名
    'auth_group_access'  => 'muucmf_auth_group_access',                       // 用户-用户组关系表
    'auth_rule'          => 'muucmf_auth_rule',                               // 权限规则表
    'auth_user'          => env('auth.auth_user', 'muucmf_member'),           // 用户信息表
    'auth_key'           => env('auth.auth_key', 'muucmf'),                   // 系统用户非常规MD5加密key
    'auth_administrator' => 1,                                                // 管理员用户ID
];