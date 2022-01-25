<?php
namespace app\common\model;

/**
 * 付费会员模型
 */
class Vip extends Base
{

    public $_status  = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '删除',
    ];
}