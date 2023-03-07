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
            $referer = request()->header()['referer'];
            $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
            $result = ['code' => 0, 'msg'  => '您没有操作权限，请联系管理员！', 'data' => [], 'url'  => $referer, 'wait' => 3,];
            if ($type == 'html') {
                $response = view(config('app.dispatch_error_tmpl'), $result);
            } else if ($type == 'json') {
                $response = json($result);
            }
            throw new \think\exception\HttpResponseException($response);
        }
        
        return $next($request);
    }
}