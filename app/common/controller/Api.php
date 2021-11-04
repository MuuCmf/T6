<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Api.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/3
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\controller;
use think\facade\Cache;
use think\helper\Str;

class Api extends Base{
    protected $middleware = [
        'app\\common\\middleware\\CheckParam',
    ];
    public $shopid;//店铺ID
    public $module;//请求的应用
    function initialize()
    {
        $params = request()->param();
        $this->shopid = $params['shopid'];
        $this->module = $params['app'];
    }
}