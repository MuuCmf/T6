<?php
namespace app\common\middleware;

use app\common\controller\Api;
use think\Response;

class CheckParam extends Api{
    protected $need_param = [
        'shopid' => '店铺ID',
    ];
    /**
     * 参数鉴权
     */
    public function handle($request, \Closure $next): Response
    {
        //获取参数
        foreach ($this->need_param as $k => $item){
            if (!$request->has($k)){
                return $this->result(0,'缺少' . $item . '参数');
            }
        }
        return $next($request);
    }
}