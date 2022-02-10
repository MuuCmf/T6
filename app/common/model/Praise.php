<?php
namespace app\common\model;

class Praise extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * 获取点赞
     *
     * @param      <type>  $info_id    The information identifier
     * @param      <type>  $info_type  The information type
     *
     * @return     <type>  The favorites.
     */
    public function getPraise($app, $info_id, $info_type ,$shopid = 0)
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
     * 判断用户是否点赞
     */
    public function yesPraise($uid, $info_id, $info_type , $app ,$shopid = 0)
    {
        if(!empty($shopid) && $shopid != 0){
            $map[] = ['shopid', '=', $shopid];
        }
        $map[] = ['app', '=', $app];
        $map[] = ['uid', '=', $uid];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $map[] = ['status', '=', 1];
        //判断是否点赞
        $data = $this->getDataByMap($map);

        return $data;
    }


}