<?php

namespace app\common\model;

class Favorites extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 获取收藏数量
     * @param int $shopid 商铺ID
     * @param string $app 应用名称
     * @param int $info_id 信息ID
     * @param string $info_type 信息类型
     * @return int 返回收藏数量
     */
    public function getFavorites($shopid, $app, $info_id, $info_type)
    {
        if (!empty($shopid) && $shopid != 0) {
            $map[] = ['shopid', '=', $shopid];
        }
        $map[] = ['app', '=', $app];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $count = $this->where($map)->count();

        return $count;
    }

    /**
     * 检查用户是否有收藏
     * @param int $shopid 商铺ID
     * @param string $app 应用名称
     * @param int $uid 用户ID
     * @param int $info_id 信息ID
     * @param string $info_type 信息类型
     * @return array 收藏数据
     */
    public function yesFavorites($shopid, $app, $uid, $info_id, $info_type)
    {
        if (!empty($shopid) && $shopid != 0) {
            $map[] = ['shopid', '=', $shopid];
        }
        $map[] = ['app', '=', $app];
        $map[] = ['uid', '=', $uid];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $map[] = ['status', '=', 1];
        //判断是否收藏
        $data = $this->getDataByMap($map);

        return $data;
    }
}
