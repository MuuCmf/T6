<?php
namespace app\common\controller;

use thans\jwt\facade\JWTAuth;

class Api extends Base{

    public $shopid = 0;//店铺ID
    public $module;//请求的应用
    public $uid = 0; // 用户id
    public $params;//参数

    function __construct()
    {
        parent::__construct();
        $this->params = request()->param();
        $this->shopid = $params['shopid'] ?? 0;
        $this->initModuleName();
        $this->checkToken();
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName(){
        $this->module = $this->params['app'] ?? App('http')->getName();
    }

    /**
     * 判断是否带token请求
     */
    protected function checkToken()
    {
        $header = request()->header();
        if(isset($header['token'])){
            $token = $header['token'];
            $payload = JWTAuth::auth(false);
            $uid = $payload['uid']->getValue();
            $this->uid = $uid;
        }
    }
}