<?php
namespace app\channel\model;

use app\common\model\Base;

class BaiduMpConfig extends Base
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
            ['shopid' ,'=' ,$shopid],
        ])->find();
        
        return $config;
    }
}