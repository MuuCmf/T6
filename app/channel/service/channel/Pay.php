<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Pay.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/2/21
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\channel\service\channel;

class Pay{
    //支付服务类
    protected $_class_name = [
        'weixin_h5' => 'WechatPayment',
        'weixin_app' => 'WechatPayment',
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
    public function init($appid ,$channel ,$shopid){
        //获取实例化的服务
        $pay_namespace = "app\\channel\\service\\pay\\{$this->_class_name[$channel]}";
        $this->server = new $pay_namespace($appid);
        return $this;
    }
}