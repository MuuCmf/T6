<?php
namespace app\channel\service\wechat;
use think\facade\Cache;

/**
 * 微信服务
 * Class Wechat
 * @package app\common\service
 */
abstract class Wechat{
    public $title;
    public $type;
    public $app;
    public $separator;
    public $config;
    public $shopid;
    public $module;
    public function __construct($app)
    {
        $this->separator = DIRECTORY_SEPARATOR;
        $this->app = $app;
    }
    public function log(){

        $log['level'] = 'debug'; //可选项debug
        $log['file'] = app()->getRootPath() . "runtime/wechatservice/{$this->type}/";
        $log['file'] .=  date('Y') . '-' . date('m') . '/';
        $log['file'] .= date('d') . '.log';
        return $log;
    }
    abstract function initConfig();

    /**
     * 获取实例化app
     * @return mixed
     */
    public function getApp(){
        return $this->app;
    }
}