<?php

namespace app\common\service\pay;

abstract class PayService
{
    protected $app;
    public $config;
    public $type;
    public $sandbox; //沙箱模式
    public $module; //模块
    public $shopid; //店铺Id
    public $platform; //平台

    public function __construct($app)
    {
        $this->app = $app;
        $this->sandbox = false; //开启沙箱模式
    }
    abstract function pay($data);
    abstract function refund($order);
    abstract function notify($params);
}
