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
        'content' => 'require'
    ];
    
    protected $message = [
        'title'  =>  '标题不能为空',
        'description' => '简短描述不能为空',
        'content' => '内容不能为空'
    ];

    protected $scene = [
        // 公告
        'announce'  =>  ['title', 'content'],
        // 消息内容
        'message'   =>  ['title', 'description', 'content']
    ];    
    
}