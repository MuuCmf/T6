<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\logic\Config as ConfigLogic;
use app\channel\logic\OfficialAccount;
use app\channel\model\WechatConfig;

class Config extends Api
{
    protected $ConfigLogic;

    function __construct()
    {
        parent::__construct();
        $this->ConfigLogic = new ConfigLogic();
    }

    /**
     * @title 获取前台系统配置
     */
    public function system()
    {
        $config = $this->ConfigLogic->frontend();
        return $this->success('success', $config);
    }
}
