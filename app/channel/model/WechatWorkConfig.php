<?php

namespace app\channel\model;

use app\common\model\Base;

class WechatWorkConfig extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 获取配置
     */
    public function getConfigByShopId($shopid = 0)
    {
        // 获取配置
        $config = $this->where([
            ['shopid', '=', $shopid],
        ])->find();
        
        if(!empty($config)){
            $config['appid'] = $config['corp_id'] ?? '';
        }
        $config['url'] = $this->callbackUrl($shopid);

        return $config;
    }

    /**
     * @title 获取回调地址
     * @return string
     */
    public function callbackUrl($shopid = 0)
    {
        return url('channel/work/callback', ['shopid' => $shopid], false, true);
    }
}
