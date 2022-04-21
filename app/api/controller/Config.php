<?php
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