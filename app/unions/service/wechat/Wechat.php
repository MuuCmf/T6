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
namespace app\unions\service\wechat;
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
    public function __construct($app)
    {
        $this->separator = DIRECTORY_SEPARATOR;
        $this->app = $app;
    }
    public function log(){

        $log['level'] = 'debug';
        $log['file'] = app()->getRootPath() . "runtime{$this->separator}wechat{$this->separator}{$this->type}{$this->separator}";
        $log['file'] .=  date('Y') . '-' . date('m') . $this->separator;
        $log['file'] .= mktime(0,0,0,date('m'),date('d'),date('Y')) . '.log';
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