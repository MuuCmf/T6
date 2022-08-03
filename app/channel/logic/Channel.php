<?php
namespace app\channel\logic;

use app\common\logic\Base;

class Channel extends Base
{
    /**
     * 来源渠道
     * @var [type]
     */
    public static $_channel = [
        'weixin_h5' => '微信公众号',
        'weixin_mp' => '微信小程序',
        'alipay' => '支付宝',
        'pc' => 'pc端',
        'admin' => '管理端'
    ];
}