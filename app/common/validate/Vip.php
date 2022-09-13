<?php
namespace app\common\validate;
 
use think\Validate;
 
/**
 * 验证器
 */
class Vip extends Validate
{
    protected $rule = [
        'card_id'  =>  'require|egt:1',
        'uid' => 'require|egt:1'
    ];
    
    protected $message  =   [
        'card_id.require' =>  '卡项未选择',
        'card_id.egt' =>  '卡项未选择',
        'uid.require' => '用户未选择',
        'uid.egt' => '用户未选择'
    ];
    
    protected $scene = [
        'edit'   =>  ['card_id','uid'],
    ];
}
