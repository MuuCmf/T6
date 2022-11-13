<?php
namespace app\common\validate;
 
use think\Validate;
 
/**
 * 验证器
 */
class Address extends Validate
{
    protected $rule = [
        'name'  =>  'require|egt:1',
        'uid' => 'require|egt:1',
        'phone'  =>  'require|mobile',
        'pos_province'  =>  'require',
        'pos_city'  =>  'require',
        'pos_district'  =>  'require',
        'address'  =>  'require',
    ];
    
    protected $message  =   [
        'name.require' =>  '姓名不能为空',
        'name.egt' =>  '姓名不能为空',
        'uid.require' => '用户ID为空',
        'uid.egt' => '用户ID为空',
        'phone.require' => '手机号不能为空',
        'phone.mobile' => '手机号格式错误',
        'pos_province.require' => '省份未选择',
        'pos_city.require' => '城市未选择',
        'pos_district.require' => '区县未选择',
        'address.require' => '详细地址不能为空',

    ];
    
    protected $scene = [
        'edit'   =>  ['uid','name','phone','pos_province','pos_city','pos_district','address'],
    ];
}
