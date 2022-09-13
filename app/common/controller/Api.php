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

    function __construct()
    {
        parent::__construct();
        // header('Content-Type: text/html;charset=utf-8');
        // header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
        // header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
        // header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
        // header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin'); // 设置允许自定义请求头的字段
    
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