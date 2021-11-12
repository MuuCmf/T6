<?php
declare (strict_types=1);

namespace app\common\middleware;

use think\Response;
use app\common\controller\Base;
use thans\jwt\JWTAuth as Auth;
use think\facade\Config;
use think\facade\Cookie;
use thans\jwt\exception\TokenExpiredException;

class CheckAuth extends Base
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    protected function setAuthentication($response, $token = null)
    {
        $token = $token ?: $this->auth->refresh();
        $this->auth->setToken($token);

        if (in_array('cookie', Config::get('jwt.token_mode'))) {
            Cookie::set('token', $token);
        }
        
        if (in_array('header', Config::get('jwt.token_mode'))) {
            $response = $response->header(['Authorization' => 'Bearer '.$token]);
        }

        return $response;
    }

    /**
     * Auth鉴权
     */
    public function handle($request, \Closure $next): Response 
    {
        // 验证token
        try {
            //$this->auth->auth();
            $payload = $this->auth->auth();
            $request->uid = $payload['uid']->getValue();

            return $next($request);
            
        } catch (TokenExpiredException $e) { // 捕获token过期
            // 尝试刷新token
            try {
                $token = $this->auth->refresh();

                $payload = $this->auth->auth(false);
                $request->uid = $payload['uid']->getValue();

                $response = $next($request);
                //通过header返回新token
                return $this->setAuthentication($response, $token);

            } catch (\Exception $e) { // 捕获黑名单宽限期
                return $this->error('需要登录','login');
            }
        } catch (\Exception $e) { // 捕获黑名单宽限期
            return $this->error($e->getMessage(),'login');
        }

        return $next($request);
    }

    
}