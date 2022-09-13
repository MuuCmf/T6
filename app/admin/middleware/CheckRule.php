<?php

namespace app\admin\middleware;

use think\App;
use think\facade\Request;
use think\Response;
use app\common\controller\Base;

class CheckRule extends Base
{
    public function handle($request, \Closure $next)
    {
        // 调试阶段放行
        return $next($request);
        // 检测访问权限 
        $rule = strtolower(app('http')->getName() . '/' . Request()->controller() . '/' . Request()->action());

        if (!$this->checkRule($rule, $this->request->uid)) {
            return $this->result(0,'无权限');
        }

        return $next($request);
    }

    
 
}