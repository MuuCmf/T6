<?php

use think\facade\Config;

if (!function_exists('get_config_type_list')) {
    /**
     * 配置类型列表
     */
    function get_config_type_list()
    {
        $list = [
            'num' => '数字',
            'string' => '文本',
            'textarea' => '多行文本',
            'select' => '下拉框',
            'editor' => '富文本',
            'checkbox' => '多选',
            'radio' => '单选',
            'color' => '颜色',
            'password' => '密码',
            'pic' => '图片',
            'file' => '文件',
            'entity' => '枚举',
            'style' => '风格',
        ];

        return $list;
    }
}

if (!function_exists('get_config_type')) {
    /**
     * 获取配置的类型
     * @param string $type 配置类型
     * @return string
     */
    function get_config_type($type = '')
    {
        $list = get_config_type_list();

        if (empty($list[$type])) {
            $list[$type] = '未设置';
        }
        return $list[$type];
    }
}

if (!function_exists('get_config_group')) {
    /**
     * 获取系统配置的分组
     * @param string $group 配置分组
     * @return string
     */
    function get_config_group(string $group)
    {
        $list = Config::get('system.CONFIG_GROUP_LIST');
        if (empty($list[$group])) {
            $list[$group] = '未设置';
        }
        
        return $list[$group];
    }
}

if (!function_exists('get_extend_config_group')) {
    /**
     * 获取系统扩展配置的分组
     * @param string $group 配置分组
     * @return string
     */
    function get_extend_config_group(string $group)
    {
        $list = Config::get('extend.GROUP_LIST');
        if (empty($list[$group])) {
            $list[$group] = '未设置';
        }

        return $list[$group];
    }
}
