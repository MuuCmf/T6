<?php
namespace app\common\controller;

use think\Request;
use thans\jwt\exception\JWTException;
use thans\jwt\facade\JWTAuth;

class Api extends Base
{
    public $shopid = 0;//店铺ID
    public $module;//请求的应用
    public $app_name; 
    public $params;//参数

    public function __construct()
    {
        $this->params = request()->param();
        $this->shopid = $params['shopid'] ?? 0;
        $this->initModuleName();
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName()
    {
        $this->module = $this->app_name = $this->params['app'] ?? App('http')->getName();
    }
}