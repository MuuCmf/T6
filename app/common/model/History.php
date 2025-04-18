<?php
namespace app\common\model;

class History extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true;

    // 设置json类型字段
    //protected $json = ['metadata'];

    /**
     * 获取历史记录数量
     * @param int $shopid 商店ID
     * @param string $app 应用名称
     * @param int $info_id 信息ID
     * @param string $info_type 信息类型
     * @return int 返回符合条件的历史记录数量
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
     * 检查用户是否有历史记录
     * @param int $shopid 商店ID
     * @param string $app 应用名称
     * @param int $uid 用户ID
     * @param int $info_id 信息ID
     * @param string $info_type 信息类型
     * @return array 历史记录数据
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
     * 添加历史记录日志
     * @param int $shopid 商户ID
     * @param string $app 应用标识
     * @param int $uid 用户ID
     * @param int $info_id 信息ID
     * @param string $info_type 信息类型
     * @param array $metadata 元数据
     * @return mixed 添加结果
     */
    public function addLog($shopid, $app, $uid, $info_id, $info_type, $metadata)
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
     * 获取今日统计数量
     * @param int $shopid 商店ID
     * @param string $app 应用名称
     * @return int 返回统计数量
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
     * 获取指定店铺一周内的历史记录数量
     * @param int $shopid 店铺ID,默认为0
     * @param string $app 应用标识,默认为空
     * @return int 返回记录数量
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
     * 获取指定店铺在当月的历史记录数量
     * @param int $shopid 店铺ID,默认为0
     * @param string $app 应用标识,默认为空
     * @return int 返回记录数量
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
     * 获取今日浏览记录数量统计的JSON数据
     * @param int $shopid 商铺ID
     * @param string $app 应用标识
     * @return string 返回JSON格式的今日24小时订单数量统计数据
     * 数据格式:
     * {
     *   "time": ["0:00-1:00", "1:00-2:00", ...], 
     *   "count": [订单数量1, 订单数量2, ...]
     * }
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
     * 获取指定商店和应用在本周内每天的历史记录统计数据
     * 
     * @param int $shopid 商店ID，默认为0
     * @param string $app 应用名称，默认为空
     * @return string 返回JSON格式的统计数据，包含:
     *                - time: 周一至周日的标识数组
     *                - count: 对应每天的记录数量数组
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
     * 获取指定店铺和应用的月度统计数据(JSON格式)
     * @param int $shopid 店铺ID，默认为0
     * @param string $app 应用名称，默认为空
     * @return string 返回JSON格式的月度统计数据,包含每日的日期和计数
     * 数据格式:
     * {
     *   "time": ["1日","2日",...],
     *   "count": [数量1,数量2,...]
     * }
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
     * 获取年度统计数据（JSON格式）
     * @param int $shopid 商店ID
     * @param string $app 应用名称
     * @return string 返回JSON格式的年度每月统计数据
     * 数据格式:
     * {
     *   "time": ["1月","2月",...,"12月"],
     *   "count": [数量1,数量2,...,数量12]
     * }
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