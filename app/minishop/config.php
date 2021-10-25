<?php
return [
    // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str'  => [
        '__ZUI__'             => STATIC_URL . '/common/lib/zui-1.9.0',
        '__SWIPER__'          => STATIC_URL . '/common/lib/Swiper-3.4.2',
        '__COMMON__'          => STATIC_URL . '/common',
        '__LIB__'             => STATIC_URL . '/knowledge/lib',
        '__IMG__'             => STATIC_URL . '/knowledge/images',
        '__ADMINJS__'    	  => STATIC_URL . '/knowledge/admin/js',
        '__ADMINIMG__'        => STATIC_URL . '/knowledge/admin/images',
        '__ADMINCSS__'        => STATIC_URL . '/knowledge/admin/css',
        '__MOBILEJS__'    	  => STATIC_URL . '/knowledge/mobile/js',
        '__MOBILEIMG__'       => STATIC_URL . '/knowledge/mobile/images',
        '__MOBILECSS__'       => STATIC_URL . '/knowledge/mobile/css',
        '__MUI__'             => STATIC_URL . '/knowledge/lib/mui',
    ],
];