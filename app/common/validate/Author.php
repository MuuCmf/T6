<?php
namespace app\common\validate;
 
use think\Validate;
 
/**
 * 创作者验证器
 */
class Author extends Validate
{
    protected $rule = [
        'name'  =>  'require',
        'description'  =>  'require',
        'professional'  =>  'require',
        'group_id' => 'require|gt:0',
        'content'  =>  'require',
    ];
    
    protected $message  =   [
        'name.require' =>  '真实姓名不能为空',
        'description.require' => '简短描述不能为空',
        'professional.require' => '职称不能为空',
        'group_id.require' => '创作者类型未选择',
        'group_id.gt' => '创作者类型未选择',
        'content.require' => '详情描述不能为空',

    ];
    
    protected $scene = [
        'edit'   =>  ['name','description','professional','group_id','content'],
    ];
}
