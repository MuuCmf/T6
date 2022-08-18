<?php
namespace app\channel\model;

use app\common\model\Base;

/**
 * 公众号配置表
 * Class WechatConfig
 * @package app\channel\model
 */
class WechatConfig extends Base{

    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 
    /**
     * @title 根据shopid获取公众号配置
     * @param int $shopid
     * @return WechatConfig|array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getWechatConfigByShopId($shopid = 0)
    {
        $res = $this->where(
            [
                ['shopid','=',$shopid]
            ]
        )->find();
        if ($res){
            $res = $res->toArray();
            return $res;
        }else{
            //初始化数据
            $data['id'] = 0;
            $data['title'] = '';
            $data['cover'] = '';
            $data['desc'] = '';
            $data['qrcode'] = '';
            $data['appid'] = '';
            $data['secret'] = '';

            return $data;
        }
    }

    /**
     * @title 获取回调地址
     * @return string
     */
    public function callbackUrl(){
        return request()->domain() . "/channel/official/callback";
    }
}