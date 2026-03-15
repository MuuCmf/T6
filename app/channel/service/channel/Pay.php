<?php
namespace app\channel\service\channel;

class Pay
{
    //支付服务类
    protected $_class_name = [
        'weixin' => 'WechatPayment',
        'alipay' => 'AlipayPayment',
    ];

    public $server;//支付服务

    /**
     * @title 初始化支付服务
     * @param $appid
     * @param $channel
     * @param $shopid
     * @return $this
     */
    public function init($appid ,$pay_channel)
    {
        //获取实例化的服务
        $pay_namespace = "app\\channel\\service\\pay\\{$this->_class_name[$pay_channel]}";
        $this->server = new $pay_namespace($appid);
        return $this;
    }
}