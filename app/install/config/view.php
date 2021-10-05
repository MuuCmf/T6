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
        '__ZUI__' => '/static/common/lib/zui',
        '__CSS__' => '/static/install/css',
        '__JS__' => '/static/install/js',
        '__IMG__' => '/static/install/images',
        '__NAME__'=>'MuuCmf开源低代码应用开发框架',
        '__COMPANY__'=>'北京火木科技有限公司',
        '__WEBSITE__'=>'www.muucmf.cn',
        '__COMPANY_WEBSITE__'=>'www.hoomuu.cn',
        '__MUUCMF__' => 'MuuCmf T6开发框架'
    ],
];