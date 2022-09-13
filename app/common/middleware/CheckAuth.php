<?php
declare (strict_types=1);

namespace app\common\middleware;

use Closure;
use thans\jwt\exception\JWTException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;
use thans\jwt\exception\TokenExpiredException;
use thans\jwt\middleware\JWTAuth;
use think\exception\HttpException;

class CheckAuth extends JWTAuth
{
    protected $auth;

    /**
     * 刷新token
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws JWTException
     * @throws TokenBlacklistException
     * @throws TokenBlacklistGracePeriodException
     */
    public function handle($request, Closure $next): object
    {
        //判断设备类型
        if (!is_mobile()) {
            // 验证登录
            $uid = is_login();
            if (!$uid) {
                return redirect('/ucenter/common/login');
            }
            $request->uid = $uid;
            $response = $next($request);
        } else {

            header('Access-Control-Expose-Headers:Authorization,authorization');//用于暴露response中的token，h5因w3c规范导致获取不到

            try {
                $payload = $this->auth->auth();
            } catch (TokenExpiredException $e) { // 捕获token过期
                // 尝试刷新token，会将旧token加入黑名单
                try {
                    $this->auth->setRefresh();
                    $token = $this->auth->refresh();
                    $payload = $this->auth->auth(false);
                } catch (TokenBlacklistGracePeriodException $e) {
                    $payload = $this->auth->auth(false);
                } catch (JWTException $exception) {
                    // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                    echo json_encode(['code' => 0, 'data' => 'login', 'msg' => $exception->getMessage()]);
                    exit();
                }
            } catch (TokenBlacklistGracePeriodException $e) { // 捕获黑名单宽限期
                $payload = $this->auth->auth(false);
            } catch (TokenBlacklistException $e) { // 捕获黑名单，退出登录或者已经自动刷新，当前token就会被拉黑
                echo json_encode(['code' => 0, 'data' => 'login', 'msg' => $e->getMessage()]);
                exit();
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                echo json_encode(['code' => 0, 'data' => 'login', 'msg' => $exception->getMessage()]);
                exit();
            }

            // 可以获取payload里自定义的字段，比如uid
            $uid = $payload['uid']->getValue();
            $request->uid = $uid;

            $response = $next($request);

            // 如果有新的token，则在响应头返回（前端判断一下响应中是否有 token，如果有就直接使用此 token 替换掉本地的 token，以此达到无痛刷新token效果）
            if (isset($token)) {
                $this->setAuthentication($response, $token);
            }
        }
        return $response;
    }


}