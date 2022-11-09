<?php
namespace app\common\controller;

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
        $this->getUid();
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName()
    {
        $this->module = $this->app_name = $this->params['app'] ?? App('http')->getName();
    }

    /**
     * 获取uid
     */
    protected function getUid()
    {
        $header = request()->header();
        if(isset($header['authorization'])){
            header('Access-Control-Expose-Headers:Authorization,authorization');//用于暴露response中的token，h5因w3c规范导致获取不到

            $token = JWTAuth::getToken();
            if(!empty($token)){
                try{
                    $payload = JWTAuth::decode($token);
                    $uid = $payload['uid']->getValue();
                }catch (JWTException $exception) {
                    // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                    $uid = 0;
                }

                request()->uid = $uid;
            }
        }
    }
}