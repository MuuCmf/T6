<?php
declare (strict_types=1);

namespace app\admin\middleware;

use think\App;
use think\Response;
use think\facade\Request;
use app\common\controller\Base;

class Auth extends Base
{
    /**
     * Auth鉴权
     */
    public function handle($request, \Closure $next): Response {
        //判断登陆
        $uid = is_login();
        if (empty($uid)) {// 还没登录 跳转到登录页面
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