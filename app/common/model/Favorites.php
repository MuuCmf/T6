<?php
namespace app\common\model;

class Favorites extends BaseModel
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * 获取收藏量
     *
     * @param      <type>  $info_id    The information identifier
     * @param      <type>  $info_type  The information type
     *
     * @return     <type>  The favorites.
     */
    public function getFavorites($shopid = 0, $app, $info_id, $info_type)
    {   
        if(!empty($shopid) && $shopid != 0){
            $map[] = ['shopid', '=', $shopid];
        }
        $map[] = ['app', '=', $app];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $count = $this->where($map)->count();

        return $count;
    }

    /**
     * 判断用户是否收藏
     */
    public function yesFavorites($shopid = 0, $app, $uid, $info_id, $info_type)
    {
        if(!empty($shopid) && $shopid != 0){
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