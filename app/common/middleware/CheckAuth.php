<?php
declare (strict_types=1);

namespace app\common\middleware;

use think\App;
use think\Response;
use think\facade\Request;
use app\common\controller\Base;
use thans\jwt\facade\JWTAuth;

class CheckAuth extends Base
{
    /**
     * Auth鉴权
     */
    public function handle($request, \Closure $next): Response {

        //可以获取请求中的完整token字符串
        $token = JWTAuth::token();
        //判断登陆
        if($token){
            //可验证token, 并获取token中的payload部分
            $payload = JWTAuth::auth(); 
            //可以继而获取payload里自定义的字段，比如uid
            $uid = $payload['uid']->getValue(); 
        }
        // 还没登录 提示登录
        if (empty($uid)) {
            return $this->result(0,'需要登录');
        }
        // 检测访问权限
        $rule = strtolower(app('http')->getName() . '/' . Request()->controller() . '/' . Request()->action());
        //dump(AuthRule::RULE_URL);exit;
        if (!$this->checkRule($rule, ['in', '1,2'])) {
            return $this->result(0,'无权限');
        }

        return $next($request);
    }

    /**
     * 权限检测
     * @param string $rule 检测的规则
     * @param string $mode check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type = AuthRule::RULE_URL, $mode = 'url')
    {
        /*
        if ($this->is_root) {
            return true;//管理员允许访问任何页面
        }*/
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \muucmf\Auth();
        }
        if (!$Auth->check($rule, is_login(), $type, $mode)) {
            return false;
        }
        return true;
    }
}