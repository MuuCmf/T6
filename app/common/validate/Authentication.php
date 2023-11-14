<?php
namespace app\common\validate;
 
use think\Validate;
 
/**
 * 验证器
 */
class Authentication extends Validate
{
    protected $rule = [
        'name'  =>  'require|egt:1',
        'uid' => 'require|egt:1',
        'card_type'  =>  'require',
        'card_no'  =>  'require|egt:1',
        'front'  =>  'require',
        'back'  =>  'require',
    ];
    
    protected $message  =   [
        'name.require' =>  '姓名不能为空',
        'name.egt' =>  '姓名不能为空',
        'uid.require' => '用户ID为空',
        'uid.egt' => '用户ID为空',
        'card_type.require' => '证件类型不能为空',
        'card_no.require' => '证件号码不能为空',
        'card_no.egt' => '证件号码不能为空',
        'front.require' => '证件图片不能为空',
        'back.require' => '证件图片不能为空',
    ];
    
    protected $scene = [
        
    ];
}
