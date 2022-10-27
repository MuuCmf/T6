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
        'h5'           => 'H5',
        'weixin_h5'    => '微信公众号',
        'weixin_mp'    => '微信小程序',
        'douyin_mp'    => '抖音小程序',
        'alipay_mp'    => '支付宝小程序',
        'baidu_mp'     => '百度小程序',
        'kuaishou_mp'  => '快手小程序',
        'pc'           => 'pc端',
        'admin'        => '管理端'
    ];
}