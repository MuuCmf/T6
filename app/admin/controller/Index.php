<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\facade\Db;
use think\facade\View;

class Index extends Admin
{
    /**
     * 控制台首页
     * @return [type] [description]
     */
    public function index()
    {
        if(request()->isPost()){
            $count_day=input('post.count_day', config('COUNT_DAY'),'intval',7);
            if(Db::name('Config')->where(['name'=>'COUNT_DAY'])->setField('value',$count_day)===false){
                return $this->error('发生错误');
            }else{
               cache('DB_CONFIG_DATA',null);
               return $this->success('设置成功');
            }

        }else{
            $this->setTitle('管理后台');
            $this->getRegUser();
            $this->getActionLog();
            $this->getUserCount();

            return View::fetch('');
        }
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
        $reg_users_map[] = ['reg_time','>=',$start];
        $reg_users_map[] = ['reg_time','<=',$end];
        $reg_users = Db::name('UcenterMember')->where($reg_users_map)->count();
        
        //进入登录用户
        $login_users_map[] = ['status', '=', 1];
        $login_users_map[] = ['last_login_time','>=',$start];
        $login_users_map[] = ['last_login_time','<=',$end];
        $login_users = Db::name('UcenterMember')->where($login_users_map)->count();
        //总用户数
        $total_user = Db::name('UcenterMember')->count();
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
        $count_day = config('COUNT_DAY');

        //每日注册用户
        for ($i = $count_day; $i--; $i >= 0) {
            $day = $today - $i * 86400;
            $day_after = $today - ($i - 1) * 86400;
            $week_map = [
                'Mon' => lang('_MON_'), 
                'Tue' => lang('_TUES_'), 
                'Wed' => lang('_WEDNES_'), 
                'Thu' => lang('_THURS_'), 
                'Fri' => lang('_FRI_'), 
                'Sat' => lang('_SATUR_'), 
                'Sun' => lang('_SUN_')
            ];
            $week[] = date('m月d日 ', $day) . $week_map[date('D', $day)];

            $map['status']=1;
            $map['reg_time']=[['>=',$day],['<=',$day_after],'and'];
            $user = Db::name('UcenterMember')->where($map)->count() * 1;
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
                'Mon' => lang('_MON_'), 
                'Tue' => lang('_TUES_'), 
                'Wed' => lang('_WEDNES_'), 
                'Thu' => lang('_THURS_'), 
                'Fri' => lang('_FRI_'), 
                'Sat' => lang('_SATUR_'), 
                'Sun' => lang('_SUN_')
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
}
