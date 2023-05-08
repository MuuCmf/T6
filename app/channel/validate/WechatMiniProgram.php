<?php

namespace app\channel\validate;

use think\Validate;

/**
 * 验证器
 */
class WechatMiniProgram extends Validate
{
    protected $rule = [
        'title'  =>  'require|egt:1',
        'description' => 'require|egt:1',
        'appid'  =>  'require',
        'secret'  =>  'require',
    ];

    protected $message  =   [
        'title.require' =>  '小程序名称不能为空',
        'title.egt' =>  '小程序名称不能为空',
        'description.require' => '小程序描述不能为空',
        'description.egt' => '小程序描述不能为空',
        'appid.require' => 'APPID不能为空',
        'secret.require' => 'Appsecret不能为空',
    ];

    protected $scene = [
        'edit'   =>  ['title', 'description', 'appid', 'secret'],
    ];
}
