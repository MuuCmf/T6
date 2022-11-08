<?php

namespace app\admin\middleware;

use app\common\controller\Base;

class CheckRule extends Base
{
    public function handle($request, \Closure $next)
    {
        $isRoot = 0;
        $uid = get_uid();
        if($uid == 1){
            $isRoot = 1;
        }
        if (!$uid) {
            // 跳转至前台登陆页
            $this->redirect(url('ucenter/common/login'));
        }
        if ($isRoot) {
            $request->isRoot = 1;
            return $next($request);
        }
        
        $Auth = new \muucmf\Auth();
        $rule = strtolower(app('http')->getName() . '/' . request()->controller() . '/' . request()->action());
        if (!$Auth->check($rule, $uid, 1, 'url')) {
            throw new \think\Exception('非法操作');
        }
        
        return $next($request);
    }
}