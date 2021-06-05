<?php

namespace app\admin\middleware;

use think\App;
use think\facade\Config;
use think\facade\Request;
use think\facade\Db;
use think\Response;
use app\common\controller\Base;

class CheckRule extends Base
{
    public function handle($request, \Closure $next)
    {
        // 检测访问权限 
        $rule = strtolower(app('http')->getName() . '/' . Request()->controller() . '/' . Request()->action());
        
        if (!$this->checkRule($rule, $this->request->uid)) {
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
    final protected function checkRule($rule, $uid, $type = 1, $mode = 'url')
    {
        /*
        if ($this->is_root) {
            return true;//管理员允许访问任何页面
        }*/
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \muucmf\Auth();
        }
        if (!$Auth->check($rule, $uid, $type, $mode)) {
            return false;
        }
        return true;
    }
 
}