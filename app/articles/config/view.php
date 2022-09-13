<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    // 视图输出字符串替换内容
    'tpl_replace_string' => [
        '__STATIC__' => '/static',
        '__COMMON__' => '/static/common',
        '__ZUI__' => '/static/common/lib/zui',
        '__LIB__' => '/static/articles/lib',
        '__IMG__' => '/static/articles/images',
        '__ADMIN_CSS__' => '/static/articles/admin/css',
        '__ADMIN_JS__' => '/static/articles/admin/js',
        '__ADMIN_IMG__' => '/static/articles/admin/images',
        '__PC_CSS__' => request()->domain() . '/static/articles/pc/css',
        '__PC_JS__' => request()->domain() . '/static/articles/pc/js',
        '__PC_IMG__' => request()->domain() . '/static/articles/pc/images',

    ],
];