<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 通用数据验证
 */
class Common extends Validate
{
    protected $rule = [
        'title'  =>  'require',
        'description' => 'require',
        'content' => 'require',
        'icon' => 'require',
        'type_id' => 'number|min:1|between:1,99999999999',
        'keyword'  =>  'require',
    ];
    
    protected $message = [
        'title'  =>  '标题不能为空',
        'description' => '简短描述不能为空',
        'content' => '内容不能为空',
        'icon' => '图标未上传',
        'type_id' => '消息类型未选择',
        'keyword' => '关键字不能为空'
    ];

    protected $scene = [
        // 公告
        'announce'  =>  ['title', 'content'],
        // 消息类型
        'message_type'   =>  ['title', 'description', 'icon'],
        // 消息发送
        'message'   =>  ['type_id', 'title', 'description', 'content'],
        // 关键字
        'keywords'  =>  ['keyword'],
    ];    
    
}