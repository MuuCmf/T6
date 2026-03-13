<?php

namespace app\common\logic;

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
        'weixin_work'  => '企业微信',
        'douyin_mp'    => '抖音小程序',
        'alipay_mp'    => '支付宝小程序',
        'baidu_mp'     => '百度小程序',
        'kuaishou_mp'  => '快手小程序',
        'pc'           => 'pc端',
        'app'          => 'app',
        'app_ios'      => '苹果app',
        'app_android'  => '安卓app',
        'app_harmony'  => '鸿蒙app',
        'admin'        => '管理端',
        ''             => '未知',
    ];
}
