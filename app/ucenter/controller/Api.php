<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Api.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/4
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\ucenter\controller;
use app\common\controller\Api as ApiBase;
class Api extends ApiBase{
    function __construct()
    {
        parent::__construct();
        //添加token验证中间件
        $this->middleware[] = 'app\\common\\middleware\\CheckAuth';
    }

    function getUserInfo(){

        echo 123;
    }
}