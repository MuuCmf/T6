<?php
namespace app\common\model;

use think\Model;

class Orders extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * Gets the data by order no.
     *
     * @param      <type>  $order_no  The order no
     *
     * @return     <type>  The data by order no.
     */
    public function getDataByOrderNo($order_no, $field='*')
    {
        $map[] = ['order_no','=',$order_no];
        $data = $this->where($map)->field($field)->find();
        if($data){
            $data = $data->toArray();
            return $data;
        }
        
        return null;
    }

    /**
     * 判断是否已购买并在有效期内
     * @param  [type]  $id   [description]
     * @param  [type]  $uid  [description]
     * @param  [type]  $type [description]
     * @param  integer $paid [description]
     * @return [type]        [description]
     */
    public function yesSale($shopid= 0, $app= '', $uid, $id, $type)
    {   
        $where = 'shopid = :shopid AND app = :app AND order_info_id = :id AND order_info_type = :type AND paid = 1 AND uid = :uid AND (end_time > :time OR end_time = 0)';
        
        //判断内容是否购买
        $res = $this->whereRaw(
            $where,
            [ 'shopid' => intval($shopid), 'app' => $app, 'id' => intval($id), 'uid' => intval($uid), 'type' => $type, 'time' => intval(time())]
        )->findOrEmpty();
        
        if(!$res->isEmpty()){
            return $res;
        }

        return false;
    }

    /**
     * 统计订单销售金额数据
     */
    public function total($shopid = 'all', $app = '')
    {
        //累计收入
        if($shopid != 'all'){
            $total_all_map[] = ['shopid', '=', $shopid];
        }
        //已支付
        $total_all_map[] = ['paid', '=', 1];
        //关联应用
        $total_all_map[] = ['app', '=', $app];
        $total_all_sum = $this->where($total_all_map)->sum('paid_fee');
        $total_all_sum = sprintf("%.2f",$total_all_sum/100);
        
        //本月收入
        $total_month_map = [
            ['app', '=', $app],
            ['paid', '=', 1],
            ['paid_time', 'between', [monthTime()[0],monthTime()[1]]],
        ];
        if($shopid != 'all'){
            $total_month_map[] = ['shopid', '=', $shopid];
        }
        $total_month_sum = $this->where($total_month_map)->sum('paid_fee');
        $total_month_sum = sprintf("%.2f",$total_month_sum/100);

        //本周收入
        $total_week_map = [
            ['app', '=', $app],
            ['paid', '=', 1],
            ['paid_time', 'between',[weekTime()[0],weekTime()[1]]],
        ];
        if($shopid != 'all'){
            $total_week_map[] = ['shopid', '=', $shopid];
        }
        $total_week_sum = $this->where($total_week_map)->sum('paid_fee');
        $total_week_sum = sprintf("%.2f",$total_week_sum/100);

        //本日收入
        $total_today_map = [
            ['app', '=', $app],
            ['paid', '=', 1],
            ['paid_time', 'between',[dayTime()[0],dayTime()[1]]],
        ];
        if($shopid != 'all'){
            $total_today_map[] = ['shopid', '=', $shopid];
        }
        $total_today_sum = $this->where($total_today_map)->sum('paid_fee');
        $total_today_sum = sprintf("%.2f",$total_today_sum/100);

        return [
            'all_sum' => $total_all_sum,
            'month_sum' => $total_month_sum,
            'week_sum' => $total_week_sum,
            'day_sum' => $total_today_sum
        ];
    }

    /**
     * 获取今天订单数量
     */
    public function getTodayOrdersCount($shopid = 0, $app = '')
    {
        $today_time = yestodayTime();
        $map = [
            ['shopid','=',$shopid],
            ['paid_time','between',[$today_time[0],$today_time[1]]]
        ];
        if (!empty($app)){
            $map[] = ['app','=',$app];
        }
        $count = $this->where($map)->count();
        return $count;
    }

    /**
     * 获取今天总收入
     */
    public function getTodayPaidFeeSum($shopid = 0, $app = '')
    {
        $today_time = yestodayTime();
        $map = [
            ['shopid','=',$shopid],
            ['paid_time','between',[$today_time[0],$today_time[1]]]
        ];
        if (!empty($app)){
            $map[] = ['app','=',$app];
        }
        $data = $this->where($map)->sum('paid_fee');
        $data = sprintf("%.2f",$data/100);

        return $data;
    }

    /**
     * 获取本周订单总收入
     */
    public function getWeekPaidFeeSum($shopid = 0, $app = '')
    {
        list($start,$end) = weekTime();
        $map[] = ['create_time', 'between', [$start, $end]];
        $map[] = ['paid', '=', 1];
        $map[] = ['shopid','=',$shopid];
        if (!empty($app)){
            $map[] = ['app','=',$app];
        }
        $data = $this->where($map)->sum('paid_fee');
        $data = sprintf("%.2f",$data/100);

        return $data;
    }

    /**
     * 获取本月订单总收入
     */
    public function getMonthPaidFeeSum($shopid = 0, $app = '')
    {
        list($start,$end) = monthTime();
        $map[] = ['create_time', 'between', [$start, $end]];
        $map[] = ['paid', '=', 1];
        $map[] = ['shopid','=',$shopid];
        if (!empty($app)){
            $map[] = ['app','=',$app];
        }
        $data = $this->where($map)->sum('paid_fee');
        $data = sprintf("%.2f",$data/100);
        return $data;
    }

    /**
     * 获取总订单数量
     */
    public function getAllOrdersCount($shopid = 0, $app = '')
    {
        $where = [
            ['shopid','=',$shopid],
            ['paid','=',1],
        ];
        if (!empty($app)){
            $where[] = ['app','=',$app];
        }
        $count = $this->where($where)->count();
        return $count;
    }

    /**
     * 获取总订单总收入
     */
    public function getAllPaidFeeSum($shopid = 0, $app = '')
    {
        $where = [
            ['shopid','=',$shopid],
            ['paid','=',1],
        ];
        if (!empty($app)){
            $where[] = ['app','=',$app];
        }
        $data = $this->where($where)->sum('paid_fee');
        $data = sprintf("%.2f",$data/100);
        return $data;
    }

    

    /**
     * chart需要的今日24小时订单数据量结构
     */
    public function todayTotalJson($shopid = 0,$app = '')
    {
        //今日订单数量
        list($start,$end) = dayTime();
        $today_total = [];
        for ($i = 0; $i < 24; $i ++) {
            $date_start = $start + (3600 * $i);
            $date_end = $start + (3600 * ($i + 1));
            $today_total['time'][$i] = ($i).':00-'.($i+1).':00';
            $map = [];
            $map[] = ['create_time', 'between', [$date_start, $date_end]];
            $map[] = ['paid', '=', 1];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)){
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $today_total['count'][$i] = $count;
        }
        $today_total = json_encode($today_total);//今日24小时数据

        return $today_total;
    }

    /**
     * chart需要的本周每日订单数据量结构
     */
    public function weekTotalJson($shopid = 0,$app = '')
    {
        //本周
        list($start,$end) = weekTime();
        $week_total = [];
        for ($i = 0; $i < 7; $i ++) {
            $date_start = $start + 86400 * $i;
            $date_end = $start + 86400 * ($i + 1);
            $week_total['time'][$i] ='周'. ($i+1);
            $map = [];
            $map[] = ['create_time', 'between', [$date_start, $date_end]];
            $map[] = ['paid', '=', 1];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)){
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $week_total['count'][$i] = $count;
        }
        $week_total = json_encode($week_total);//本周每日数据

        return $week_total;
    }

    /**
     * chart需要的本月每日订单数据量结构
     */
    public function monthTotalJson($shopid = 0,$app = '')
    {
        list($start,$end) = monthTime();
        $month_total = [];
        for ($i = 0; $i < ($end + 1 - $start) / 86400; $i ++) {
            $date_start = $start + 86400 * $i;
            $date_end = $start + 86400 * ($i + 1);
            $month_total['time'][$i] = ($i+1) . '日';
            $map = [];
            $map[] = ['create_time', 'between', [$date_start, $date_end]];
            $map[] = ['paid', '=', 1];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)){
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $month_total['count'][$i] = $count;
        }
        $month_total = json_encode($month_total);//本月每日数据

        return $month_total;
    }

    /**
     * chart需要的本年每月订单数据量结构
     */
    public function yearTotalJson($shopid = 0,$app ='')
    {
        $year = date('Y', time());
        $year_total = [];
        for ($i = 0; $i < 12; $i++) {
            $date_start = strtotime($year . '-' .intval($i+1)." first day of");
            $date_end = strtotime($year . '-' .intval($i+1)." last day of") + 86400;
            $year_total['time'][$i] = ($i+1) . '月';
            $map = [];
            $map[] = ['create_time', 'between', [$date_start, $date_end]];
            $map[] = ['paid', '=', 1];
            $map[] = ['shopid', '=', $shopid];
            if (!empty($app)){
                $map[] = ['app', '=', $app];
            }
            $count = $this->where($map)->count();
            $year_total['count'][$i] = $count;
        }
        $year_total = json_encode($year_total);//本年每月数据

        return $year_total;
    }

}