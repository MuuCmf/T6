<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Config.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/3/16
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\controller\Base;
use app\common\logic\Config as ConfigLogic;

class Config extends Base
{
    protected $ConfigLogic;
    protected $params;
    function __construct()
    {
        parent::__construct();
        $this->ConfigLogic = new ConfigLogic();
        $this->params = request()->param();
    }

    /**
     * @title 获取前台系统配置
     */
    function getFrontendConfig(){
        $config = $this->ConfigLogic->frontend($this->params['shopid']);
        $this->success('success',$config);
    }
}