<?php
namespace app\articles\model;

use app\articles\model\ArticlesBase as Base;
use think\facade\Cache;
use app\articles\logic\Config as ConfigLogic;

/**
 * 应用配置
 */
class ArticlesConfig extends Base
{
    /**
     * 获取应用配置
     */
    public function getConfig($shopid = 0)
    {
        // 获取应用配置数据
        $config_data = Cache::get(request()->host() . '_MUUCMF_ARTICLES_CONFIG_DATA_' . $shopid);
        if(empty($config_data)){
            $config_data= $this->getDataByMap(['shopid' => $shopid]);
            if(empty($config_data)){
                // 设置默认值
                $config_data = $this->defaultData($shopid);
            }
            $config_data = (new ConfigLogic())->formatData($config_data);
            Cache::set(request()->host() . '_MUUCMF_ARTICLES_CONFIG_DATA_' . $shopid, $config_data);
        }

        return $config_data;
    }

    /**
     * 初始数据
     */
    private function defaultData($shopid = 0)
    {
        $data = [
            'id' => 0,
            'shopid' => $shopid,
            'status' => 1,
        ];

        return $data;
    }

}