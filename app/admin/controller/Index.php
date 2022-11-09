<?php

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Db;
use think\facade\View;
use app\admin\model\CountActive;

class Index extends Admin
{
    public function __construct()
    {
        parent::__construct();
        //应用入口
        View::assign('all_module_list', $this->allModuleList()); 
    }

    /**
     * 控制台首页
     * @return [type] [description]
     */
    public function index()
    {
        $this->getUserCount();
        $this->getRegUser();
        $this->getActionLog();
        $this->getOtherCount();

        $this->setTitle('控制台');
        // 模板输出
        return View::fetch('index');
    }

    /**
     * 获取顶部块统计数据
     * @return [type] [description]
     */
    private function getUserCount(){

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

        $count['today_user'] = $reg_users;
        $count['login_users'] = $login_users;
        $count['total_user'] = $total_user;
        $count['today_action_log'] = $today_action_log;
        
        View::assign(['count' => $count]);
    }
    
    /**
     * 最近N日用户增长
     * @return [type] [description]
     */
    private function getRegUser()
    {
        $today = date('Y-m-d', time());
        $today = strtotime($today);
        
        $week = [];
        $regMemeberCount = [];
        $count_day = Config::get('system.COUNT_DAY');

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
        $regMember = json_encode($regMember);

        View::assign(['count_day' => $count_day]);
        View::assign(['regMember' => $regMember]);
    }

    /**
     * 最近N日用户行为数据
     * @return [type] [description]
     */
    private function getActionLog()
    {
        $today = date('Y-m-d', time());
        $today = strtotime($today);
        $count_day = 7;//默认一周

        $week = [];
        $actionLogData = [];
        
        //每日用户行为数量
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
            
            $map[] = ['status','=',1];
            $map[] = ['create_time','>=',$day];
            $map[] = ['create_time','<=',$day_after];
            $user = Db::name('action_log')->where($map)->count() * 1;
            //dump($user);exit;
            $actionLogData[] = $user;
        }
        
        $actionLog['days'] = $week;
        $actionLog['data'] = $actionLogData;
        $actionLog = json_encode($actionLog);

        View::assign(['actionLog' => $actionLog]);
    }

    private function getOtherCount(){
        
        //日活跃
        $today = date('Y-m-d 00:00',time());
        $startTime = strtotime($today." - 10 day");
        $endTime = strtotime($today);
        $startTime = strtotime(date('Y-m-d').' - 9 day');

        $countActiveModel = new CountActive;
        $activeList = $countActiveModel->getActiveList($startTime,time(),'day');
        
        View::assign('activeList',json_encode($activeList));
        //周活跃
        $startTime = strtotime(date('Y-m-d').' - '.date('w').' day - 49 day');
        $weekActiveList = $countActiveModel->getActiveList($startTime,time(),'week');
        View::assign('weekActiveList',json_encode($weekActiveList));
        //月活跃
        $startTime = strtotime(date('Y-m-01').' - 9 month');
        $monthActiveList = $countActiveModel->getActiveList($startTime,time(),'month');
        View::assign('monthActiveList',json_encode($monthActiveList));
        
    }
}
