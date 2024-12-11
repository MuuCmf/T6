<?php

namespace app\common\model;

class Share extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true;


    /**
     * 获取指定店铺、应用、信息ID和信息类型的分享数量
     *
     * @param int $shopid 店铺ID
     * @param string $app 应用名称
     * @param int $info_id 信息ID
     * @param string $info_type 信息类型
     * @return int 分享数量
     */
    public function getShareCount($shopid, $app, $info_id, $info_type)
    {
        $map[] = ['shopid', '=', $shopid];
        $map[] = ['app', '=', $app];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $map[] = ['status', '=', 1];
        $count = $this->where($map)->count();

        return $count;
    }
}
