<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: CheckParam.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/4
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\middleware;
use app\common\controller\Api;
use think\Response;

class CheckParam extends Api{
    protected $need_param = [
        'shopid' => '店铺ID',
        'app' => '应用标识'
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