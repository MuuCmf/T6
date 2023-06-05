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
        $config = $this->ConfigLogic->frontend($this->shopid);
        return $this->success('success', $config);
    }

    /**
     * 获取微信公众号配置
     */
    public function weixin()
    {
        //获取公众号配置
        $weixin_h5 = (new WechatConfig())->where('shopid', $this->shopid)->field('title,desc,cover,qrcode,appid,auth_login')->find();
        if ($weixin_h5) {
            $weixin_h5 = $weixin_h5->toArray();
            $weixin_h5 = (new OfficialAccount())->formatData($weixin_h5);
        }

        return $this->success('success', $weixin_h5);
    }
}
