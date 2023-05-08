<?php

namespace app\channel\validate;

use think\Validate;

/**
 * 验证器
 */
class Account extends Validate
{
    protected $rule = [
        'title'  =>  'require|egt:1',
        'desc' => 'require|egt:1',
        'cover'  =>  'require',
        'qrcode'  =>  'require',
        'appid'  =>  'require',
        'secret'  =>  'require',
    ];

    protected $message  =   [
        'title.require' =>  '公众号名称不能为空',
        'title.egt' =>  '公众号名称不能为空',
        'desc.require' => '公众号描述不能为空',
        'desc.egt' => '公众号描述不能为空',
        'cover.require' => '公众号图标未上传',
        'qrcode.require' => '公众号二维码未上传',
        'appid.require' => 'APPID不能为空',
        'secret.require' => 'Appsecret不能为空',
    ];

    protected $scene = [
        'edit'   =>  ['title', 'desc', 'cover', 'qrcode', 'appid', 'secret'],
    ];
}
