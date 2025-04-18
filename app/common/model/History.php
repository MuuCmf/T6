<?php
namespace app\common\model;

class History extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    // 设置json类型字段
	//protected $json = ['metadata'];

    /**
     * 获取收藏量
     *
     * @param      <type>  $info_id    The information identifier
     * @param      <type>  $info_type  The information type
     *
     * @return     <type>  The favorites.
     */
    public function getHistory($shopid, $app, $info_id, $info_type)
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
     * 判断用户是否浏览
     */
    public function yesHistory($shopid, $app, $uid, $info_id, $info_type)
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

    /**
     * 增加浏览记录
     * @param $uid
     * @param $info_id
     * @param $info_type
     */
    public function addLog($shopid, $app, $uid, $info_id ,$info_type, $metadata)
    {
        // 判断是否存在记录
        $data = $this->yesHistory($shopid, $app, $uid, $info_id, $info_type);

        $id = 0;
        if($data){
            $id = $data['id'];
        }
        //写浏览记录
        $history_data = [
            'id' => $id,
            'info_id' => $info_id,
            'info_type' => $info_type,
            'uid'=> $uid,
            'shopid' => $shopid,
            'app' => $app,
            'status' => 1,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE)
        ];
        $res = $this->edit($history_data);

        return $res;
    }

    /**
     * 获取今天浏览数量
     */
    public function getTodaCount($shopid = 0, $app = '')
    {
        $today_time = dayTime();
        $map = [
            ['shopid', '=', $shopid],
            ['update_time', 'between', [$today_time[0], $today_time[1]]]
        ];
        if (!empty($app)) {
            $map[] = ['app', '=', $app];
        }
        $count = $this->where($map)->count();

        return $count;
    }

    /**
     * 获取本周浏览数量
     */
    public function getWeekCount($shopid = 0, $app = '')
    {
        list($start, $end) = weekTime();
        $map[] = ['update_time', 'between', [$start, $end]];
        $map[] = ['shopid', '=', $shopid];
        if (!empty($app)) {
            $map[] = ['app', '=', $app];
        }
        $count = $this->where($map)->count();

        return $count;
    }

    /**
     * 获取本月浏览数量
     */
    public function getMonthCount($shopid = 0, $app = '')
    {
        list($start, $end) = monthTime();
        $map[] = ['update_time', 'between', [$start, $end]];
        $map[] = ['shopid', '=', $shopid];
        if (!empty($app)) {
            $map[] = ['app', '=', $app];
        }
        $count = $this->where($map)->count();

        return $count;
    }

    /**
     * 今日24小时播放数据量结构
     */
    public function todayTotalJson($shopid = 0, $app = '')
    {
        //今日订单数量
        list($start, $end) = dayTime();
        $today_total = [];
        for ($i = 0; $i < 24; $i++) {
            $date_start = $start + (3600 * $i);
            $date_end = $start + (3600 * ($i + 1));
            $today_total['time'][$i] = ($i) . ':00-' . ($i + 1) . ':00';
            $map = [];
            $map[] = ['update_time', 'between', [$date_start, $date_end]];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)) {
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();

            $today_total['count'][$i] = $count;
        }
        $today_total = json_encode($today_total); //今日24小时数据

        return $today_total;
    }

    /**
     * 本周每日浏览数据量结构
     */
    public function weekTotalJson($shopid = 0, $app = '')
    {
        //本周
        $start = strtotime("this week monday");
        $week_total = [];
        for ($i = 0; $i < 7; $i++) {
            if($i == 0){
                $date_start = $start;
                $date_end = $start + 86400;
            }else{
                $date_start = $start + 86400 * $i;
                $date_end = $start + 86400 * ($i + 1);
            }
            $week_total['time'][$i] = '周' . ($i + 1);
            $map = [];
            $map[] = ['update_time', 'between', [$date_start, $date_end]];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)) {
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $week_total['count'][$i] = $count;
        }
        $week_total = json_encode($week_total); //本周每日数据

        return $week_total;
    }

    /**
     * 本月每日浏览数据量结构
     */
    public function monthTotalJson($shopid = 0, $app = '')
    {
        list($start, $end) = monthTime();
        $month_total = [];
        for ($i = 0; $i < ($end + 1 - $start) / 86400; $i++) {
            $date_start = $start + 86400 * $i;
            $date_end = $start + 86400 * ($i + 1);
            $month_total['time'][$i] = ($i + 1) . '日';
            $map = [];
            $map[] = ['update_time', 'between', [$date_start, $date_end]];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)) {
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $month_total['count'][$i] = $count;
        }
        $month_total = json_encode($month_total); //本月每日数据

        return $month_total;
    }

    /**
     * 本年每月浏览数据量结构
     */
    public function yearTotalJson($shopid = 0, $app = '')
    {
        $year = date('Y', time());
        $year_total = [];
        for ($i = 0; $i < 12; $i++) {
            $date_start = strtotime($year . '-' . intval($i + 1) . " first day of");
            $date_end = strtotime($year . '-' . intval($i + 1) . " last day of") + 86400;
            $year_total['time'][$i] = ($i + 1) . '月';
            $map = [];
            $map[] = ['update_time', 'between', [$date_start, $date_end]];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)) {
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $year_total['count'][$i] = $count;
        }
        $year_total = json_encode($year_total); //本年每月数据

        return $year_total;
    }

}