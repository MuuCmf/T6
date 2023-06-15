<?php

namespace app\channel\validate;

use think\Validate;

/**
 * 验证器
 */
class WechatWork extends Validate
{
    protected $rule = [
        'title'  =>  'require|egt:1',
        'description' => 'require|egt:1',
        'corp_id' => 'require',
        'agent_id'  =>  'require',
        'secret'  =>  'require',
    ];

    protected $message  =   [
        'title.require' =>  '企业名称不能为空',
        'title.egt' =>  '企业名称不能为空',
        'description.require' => '企业描述不能为空',
        'description.egt' => '企业描述不能为空',
        'corp_id.require' => 'corp_id',
        'agent_id.require' => 'agent_id不能为空',
        'secret.require' => 'secret不能为空',
    ];

    protected $scene = [
        'edit'   =>  ['title', 'description', 'corp_id', 'agent_id', 'secret'],
    ];
}
