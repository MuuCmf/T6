<?php
namespace app\common\controller;

use think\facade\Cookie;
use think\facade\Config;
use thans\jwt\exception\JWTException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;
use thans\jwt\exception\TokenExpiredException;
use thans\jwt\facade\JWTAuth;

class Api extends Base
{

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
        $this->getUid();
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName()
    {
        $this->module = $this->params['app'] ?? App('http')->getName();
    }

    /**
     * 获取uid
     */
    protected function getUid()
    {
        $header = request()->header();
        if(isset($header['authorization'])){
            header('Access-Control-Expose-Headers:Authorization,authorization');//用于暴露response中的token，h5因w3c规范导致获取不到
            try {
                $payload = JWTAuth::auth();
            } catch (TokenExpiredException $e) { // 捕获token过期
                // 尝试刷新token，会将旧token加入黑名单
                JWTAuth::setRefresh();
                $token = JWTAuth::refresh();
                $payload = JWTAuth::auth(false);
            } 

            if (isset($token)) {
                JWTAuth::setToken($token);

                if (in_array('cookie', Config::get('jwt.token_mode'))) {
                    Cookie::set('token', $token);
                }
                if (in_array('header', Config::get('jwt.token_mode'))) {
                    //response()->header(['Authorization' => 'Bearer '.$token]);
                    header('Authorization:'.'Bearer '.$token);
                }
            }
            
            $uid = $payload['uid']->getValue();
            request()->uid = $uid;
        }
    }
}