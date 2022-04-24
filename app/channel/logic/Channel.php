<?php
namespace app\channel\logic;

use app\common\logic\Base as MuuBase;

class Channel extends MuuBase{
    /**
     * 支付渠道
     * @var [type]
     */
    public static $_channel = [
        'weixin_h5' => '微信公众号支付',
        'weixin_app' => '微信小程序支付',
        'alipay' => '支付宝',
        'offline' => '线下支付',
        'score' => '积分'
    ];
}