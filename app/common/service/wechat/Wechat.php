<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Wechat.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/23
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\service\wechat;
/**
 * 微信服务
 * Class Wechat
 * @package app\common\service
 */
abstract class Wechat{
    public $title = '微信应用';
    public $type = '公众号';
    public $app = null;
    public function __construct($app)
    {
        $this->app = $app;
    }
    /**
     * 实例化
     * @return mixed
     */
//    abstract function app();

    /**
     * 支付
     * @return mixed
     */
//    abstract function payment();
    public function log(){
        $separator = DIRECTORY_SEPARATOR;
        $log['level'] = 'debug';
        $log['file'] = app()->getRuntimePath() . "wechat{$separator}{$this->type}{$separator}";
        $log['file'] .=  date('Y-M') . $separator;
        $log['file'] .= mktime(0,0,0,date('m'),date('d'),date('Y')) . '.log';
        return $log;
    }
}