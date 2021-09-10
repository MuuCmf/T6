<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;

class Count extends Admin
{
    /**
     * 获取顶部块统计数据
     * @return [type] [description]
     */
    public function count(){

        $t = time();
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));

        //进入注册
        $reg_users_map[] = ['status', '=', 1];
        $reg_users_map[] = ['create_time','>=',$start];
        $reg_users_map[] = ['create_time','<=',$end];
        $reg_users = Db::name('Member')->where($reg_users_map)->count();
        
        //进入登录用户
        $login_users_map[] = ['status', '=', 1];
        $login_users_map[] = ['last_login_time','>=',$start];
        $login_users_map[] = ['last_login_time','<=',$end];
        $login_users = Db::name('Member')->where($login_users_map)->count();
        //总用户数
        $total_user = Db::name('Member')->count();
        //今日用户行为
        $today_action_log = Db::name('ActionLog')->where('status=1 and create_time>=' . $start)->count();

        // 今日新增
        $count['today_user'] = $reg_users;
        // 今日登录
        $count['login_users'] = $login_users;
        // 总用户
        $count['total_user'] = $total_user;
        // 今日用户行为
        $count['today_action_log'] = $today_action_log;

        return $this->success('success','',$count);
    }

    /**
     * 最近N日用户增长
     * @return [type] [description]
     */
    public function reg()
    {
        $today = date('Y-m-d', time());
        $today = strtotime($today);
        
        $week = [];
        $regMemeberCount = [];
        $count_day = config::get('system.COUNT_DAY');

        //每日注册用户
        for ($i = $count_day; $i--; $i >= 0) {
            $day = $today - $i * 86400;
            $day_after = $today - ($i - 1) * 86400;
            $week_map = [
                'Mon' => '周一', 
                'Tue' => '周二', 
                'Wed' => '周三', 
                'Thu' => '周四', 
                'Fri' => '周五', 
                'Sat' => '周六', 
                'Sun' => '周日'
            ];
            $week[] = date('m月d日 ', $day) . $week_map[date('D', $day)];

            $map = [
                ['status','=',1],
                ['create_time','>=',$day],
                ['create_time','<=',$day_after]
            ];
            $user = Db::name('Member')->where($map)->count() * 1;

            $regMemeberCount[] = $user;
        }

        $regMember['days'] = $week;
        $regMember['data'] = $regMemeberCount;
        $regMember = $regMember;

        $res = [
            'count_day' => $count_day,
            'data' => $regMember
        ];

        return $this->success('success',$res);
    }




}