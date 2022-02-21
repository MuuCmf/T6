<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: PayService.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/23
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\channel\service\pay;

abstract class PayService{
    protected $app;
    public $config;
    public $type;
    public $sandbox;//沙箱模式
    public $module;//模块
    public $shopid;//店铺Id
    public $platform;//平台
    public function __construct($app)
    {
        $this->separator = DIRECTORY_SEPARATOR;
        $this->app = $app;
        $this->sandbox = false;//开启沙箱模式
    }
    abstract function pay($data);
    abstract function refund($order);
    abstract function notify($params);
}