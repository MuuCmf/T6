<?php
namespace app\common\validate;
 
use think\Validate;
 
/**
 * 验证器
 */
class Orders extends Validate
{
    protected $rule = [
        'app'  =>  'require',
        'order_info_id'  =>  'require|egt:1',
        'order_info_type'  =>  'require',
        'channel'  =>  'require',
    ];
    
    protected $message  =   [
        'app.require' =>  '缺少应用标识参数',
        'order_info_id.require' => '缺少order_info_id参数',
        'order_info_id.egt' => '缺少order_info_id参数',
        'order_info_type.require' => '缺少order_info_type参数',
        'channel' => '缺少来源渠道参数'
    ];
    
    protected $scene = [
        'edit'   =>  ['app','order_info_id','order_info_type','channel'],
    ];
}
