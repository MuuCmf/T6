<?php
use think\facade\Db;
use think\facade\Config;
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

/**
 * 获取属性类型信息
 */ 
function get_attribute_type($type = '')
{
    // TODO 可以加入系统配置
    static $_type = [
        'num' => array('数字', 'int(10) UNSIGNED NOT NULL'),
        'string' => array('字符串', 'varchar(255) NOT NULL'),
        'textarea' => array('文本框', 'text NOT NULL'),
        'datetime' => array('时间', 'int(10) NOT NULL'),
        'bool' => array('布尔', 'tinyint(2) NOT NULL'),
        'select' => array('枚举', 'char(50) NOT NULL'),
        'radio' => array('单选', 'char(10) NOT NULL'),
        'checkbox' => array('多选', 'varchar(100) NOT NULL'),
        'editor' => array('编辑器', 'text NOT NULL'),
        'picture' => array('上传图片', 'int(10) UNSIGNED NOT NULL'),
        'file' => array('上传附件', 'int(10) UNSIGNED NOT NULL'),
    ];
    
    return $type ? $_type[$type][0] : $_type;
}

/* 解析列表定义规则*/

function get_list_field($data, $grid, $model)
{

    // 获取当前字段数据
    foreach ($grid['field'] as $field) {
        $array = explode('|', $field);
        $temp = $data[$array[0]];
        // 函数支持
        if (isset($array[1])) {
            $temp = call_user_func($array[1], $temp);
        }
        $data2[$array[0]] = $temp;
    }
    if (!empty($grid['format'])) {
        $value = preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use ($data2) {
            return $data2[$match[1]];
        }, $grid['format']);
    } else {
        $value = implode(' ', $data2);
    }

    // 链接支持
    if (!empty($grid['href'])) {
        $links = explode(',', $grid['href']);
        foreach ($links as $link) {
            $array = explode('|', $link);
            $href = $array[0];
            if (preg_match('/^\[([a-z_]+)\]$/', $href, $matches)) {
                $val[] = $data2[$matches[1]];
            } else {
                $show = isset($array[1]) ? $array[1] : $value;
                // 替换系统特殊字符串
                $href = str_replace(
                    array('[DELETE]', '[EDIT]', '[MODEL]'),
                    array('del?ids=[id]&model=[MODEL]', 'edit?id=[id]&model=[MODEL]', $model['id']),
                    $href);

                // 替换数据变量
                $href = preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use ($data) {
                    return $data[$match[1]];
                }, $href);

                $val[] = '<a href="' . url($href) . '">' . $show . '</a>';
            }
        }
        $value = implode(' ', $val);
    }
    return $value;
}

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_status_title($status = null)
{
    if (!isset($status)) {
        return false;
    }
    switch ($status) {
        case -2 :
            return '审核未通过';
            break;
        case -1 :
            return '删除';
            break;
        case 0  :
            return '禁用';
            break;
        case 1  :
            return '启用';
            break;
        case 2  :
            return '未审核';
            break;
        default :
            return false;
            break;
    }
}

/**
 * 配置类型列表
 */
function get_config_type_list()
{
    // 'num' => array('数字', 'int(10) UNSIGNED NOT NULL'),
    // 'string' => array('字符串', 'varchar(255) NOT NULL'),
    // 'textarea' => array('文本框', 'text NOT NULL'),
    // 'datetime' => array('时间', 'int(10) NOT NULL'),
    // 'bool' => array('布尔', 'tinyint(2) NOT NULL'),
    // 'select' => array('枚举', 'char(50) NOT NULL'),
    // 'radio' => array('单选', 'char(10) NOT NULL'),
    // 'checkbox' => array('多选', 'varchar(100) NOT NULL'),
    // 'editor' => array('编辑器', 'text NOT NULL'),
    // 'picture' => array('上传图片', 'int(10) UNSIGNED NOT NULL'),
    // 'file' => array('上传附件', 'int(10) UNSIGNED NOT NULL'),
    $list = [
        'num' => '数字',
        'string' => '字符',
        'textarea' => '文本域',
        'entity' => '枚举',
        'select' => '下拉框',
        'editor' => '富文本',
        'password' => '密码',
        'pic' => '图片',
        'checkbox' => '多选框',
        'radio' => '单选框',
        'text' => '纯文本'
    ];
    
    return $list;
}

/**
 * 获取配置的类型
 * @param string $type 配置类型
 * @return string
 */
function get_config_type($type = '')
{
    $list = get_config_type_list();

    if(empty($list[$type])){
        $list[$type] = '未设置';
    }
    return $list[$type];
}

/**
 * 获取系统配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_config_group($group = 0)
{
    $list = Config::get('system.CONFIG_GROUP_LIST');
    return $group ? $list[$group] : '';
}

/**
 * 获取扩展配置的分组
 * @param string $group 配置分组
 * @return string
 */
function get_extend_group($group = 1)
{
    $list = Config::get('extend.GROUP_LIST');
    return $group ? $list[$group] : '';
}


function int_to_string(&$data, $map = ['status' => [1 => '启用', -1 => '删除', 0 => '禁用', -2 => '未审核', 3 => '草稿']])
{
    if ($data === false || $data === null) {
        return $data;
    }
    $data = (array)$data;
    foreach ($data as $key => $row) {
        foreach ($map as $col => $pair) {
            if (isset($row[$col]) && isset($pair[$row[$col]])) {
                $data[$key][$col . '_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

function lists_plus(&$data)
{
    $alias = Db::name('module')->select();

    foreach ($alias as $value) {
        $alias_set[$value['name']] = $value['alias'];
    }
    foreach ($data as $key => $value) {
        if(empty($data[$key]['module'])){
            $data[$key]['alias'] = '';
        }else{
            $data[$key]['alias'] = $alias_set[$data[$key]['module']];
        }
        
        $mid = Db::name('action_log')->field("max(create_time),remark")->where('action_id=' . $data[$key]['id'])->select();
        $mid_s = $mid[0]['remark'];
        if( isset($mid_s) && strpos($mid_s , lang('_INTEGRAL_')) !== false)
        {
            $data[$key]['vary'] = $mid_s;
        }else{
            $data[$key]['vary'] = '';
        }

    }
    return $data;
}

/**
 * 动态扩展左侧菜单,base.html里用到
 */
function extra_menu($extra_menu, &$base_menu)
{
    foreach ($extra_menu as $key => $group) {
        if (isset($base_menu['child'][$key])) {
            $base_menu['child'][$key] = array_merge($base_menu['child'][$key], $group);
        } else {
            $base_menu['child'][$key] = $group;
        }
    }
}

// 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string)
{
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}

// 分析枚举类型字段值 格式 a:名称1,b:名称2
// 暂时和 parse_config_attr功能相同
// 但请不要互相使用，后期会调整
function parse_field_attr($string)
{
    if (0 === strpos($string, ':')) {
        // 采用函数定义
        return eval(substr($string, 1) . ';');
    }
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if (strpos($string, ':')) {
        $value = array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k] = $v;
        }
    } else {
        $value = $array;
    }
    return $value;
}

/**
 * 获取行为数据
 * @param string $id 行为id
 * @param string $field 需要获取的字段
 * @author huajie <banhuajie@163.com>
 */
function get_action($id = null, $field = null)
{
    if (empty($id) && !is_numeric($id)) {
        return false;
    }
    $list = cache('action_list');
    if (empty($list[$id])) {
        $map[] = ['status', '>', -1];
        $map[] = ['id', '=', $id];
        $list[$id] = Db::name('Action')->where($map)->field(true)->find();
    }
    return empty($field) ? $list[$id] : $list[$id][$field];
}


/**
 * 获取行为类型
 * @param intger $type 类型
 * @param bool $all 是否返回全部类型
 * @author huajie <banhuajie@163.com>
 */
function get_action_type($type, $all = false)
{
    $list = array(
        1 => '系统',
        2 => '用户',
    );
    if ($all) {
        return $list;
    }
    return $list[$type];
}

/**
 * 对字符串执行指定次数替换
 * @param  Mixed $search   查找目标值
 * @param  Mixed $replace  替换值
 * @param  Mixed $subject  执行替换的字符串／数组
 * @param  Int   $limit    允许替换的次数，默认为-1，不限次数
 * @return Mixed
 */
function str_replace_limit($search, $replace, $subject, $limit=-1){
    if(is_array($search)){
        foreach($search as $k=>$v){
            $search[$k] = '`'. preg_quote($search[$k], '`'). '`';
        }
    }else{
        $search = '`'. preg_quote($search, '`'). '`';
    }
    return preg_replace($search, $replace, $subject, $limit);
}
