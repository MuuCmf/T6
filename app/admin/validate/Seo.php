<?php
namespace app\admin\validate;

use think\Validate;

class Seo extends Validate
{
    protected $rule = [
        'title'  =>  'require',
    ];
    
    protected $message = [
        'title'  =>  '规则名称不能为空',
    ];
    
}