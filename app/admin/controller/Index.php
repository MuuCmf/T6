<?php

namespace app\admin\controller;

use think\Exception;
use think\facade\Cache;
use think\facade\Db;

class Index extends Admin
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 控制台首页
     * @return [type] [description]
     */
    public function index()
    {
        $count = $this->getUserCount();
        $regMember = $this->getRegUser();
        $actionLog = $this->getActionLog();
        $systemInfo = $this->getSystemInfo();

        $result = [
            'count' => $count,
            'reg_member' => $regMember,
            'action_log' => $actionLog,
            'system_info' => $systemInfo,
        ];

        // json response
        return $this->success('success', $result);
    }

    /**
     * 获取顶部块统计数据
     * @return [type] [description]
     */
    private function getUserCount()
    {
        $t = time();
        $start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
        $end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));

        //今日注册用户
        $reg_users_map[] = ['status', '=', 1];
        $reg_users_map[] = ['create_time', '>=', $start];
        $reg_users_map[] = ['create_time', '<=', $end];
        $reg_users = Db::name('Member')->where($reg_users_map)->count();

        //今日登录用户
        $login_users_map[] = ['status', '=', 1];
        $login_users_map[] = ['last_login_time', '>=', $start];
        $login_users_map[] = ['last_login_time', '<=', $end];
        $login_users = Db::name('Member')->where($login_users_map)->count();
        //总用户数
        $total_user = Db::name('Member')->where('status', 'in', [0, 1])->count();
        //今日用户行为
        $today_action_log = Db::name('ActionLog')->where('status', '=', 1)->where('create_time', '>=', $start)->count();

        $count['today_user'] = $reg_users;
        $count['login_users'] = $login_users;
        $count['total_user'] = $total_user;
        $count['today_action_log'] = $today_action_log;

        return $count;
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
        $count_day = config('system.COUNT_DAY');

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
                ['status', '=', 1],
                ['create_time', '>=', $day],
                ['create_time', '<=', $day_after]
            ];
            $user = Db::name('Member')->where($map)->count() * 1;

            $regMemeberCount[] = $user;
        }

        $regMember['days'] = $week;
        $regMember['data'] = $regMemeberCount;

        $regMemberResult = [
            'count_day' => $count_day,
            'data' => $regMember
        ];

        return $regMemberResult;
    }

    /**
     * 最近N日用户行为数据
     * @return [type] [description]
     */
    private function getActionLog()
    {
        $today = date('Y-m-d', time());
        $today = strtotime($today);
        $count_day = config('system.COUNT_DAY'); //默认一周

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

            $map[] = ['status', '=', 1];
            $map[] = ['create_time', '>=', $day];
            $map[] = ['create_time', '<=', $day_after];
            $user = Db::name('action_log')->where($map)->count() * 1;
            $actionLogData[] = $user;
        }

        $actionLog['count_day'] = $count_day;
        $actionLog['days'] = $week;
        $actionLog['data'] = $actionLogData;

        return $actionLog;
    }

    private function getSystemInfo()
    {
        // 获取操作系统
        $os = PHP_OS;

        // 获取服务器软件信息
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '未知版本';

        // 获取MySQL版本号
        $mysql_version = $this->getMysqlVersion();

        // 获取Redis版本号
        $redis_version = $this->getRedisVersion();

        // 上传最大文件大小
        $upload_max_filesize = ini_get('upload_max_filesize');

        // PHP版本号
        $php_version = phpversion();

        // 系统版本号
        $version = $this->version();

        $systemInfo = [
            'os' => $os,
            'server_software' => $server_software,
            'mysql_version' => $mysql_version,
            'redis_version' => $redis_version,
            'upload_max_filesize' => $upload_max_filesize,
            'php_version' => $php_version,
            'version' => $version,
        ];

        return $systemInfo;
    }

    private function getMysqlVersion()
    {
        $version = Db::query("SELECT VERSION() as mysql_version")[0]['mysql_version'];

        return $version;
    }

    public function getRedisVersion()
    {
        try {
            // 1. 获取Redis缓存连接实例（默认使用config/cache.php中的redis配置）
            $redis = Cache::store('redis')->handler();
            // 2. 执行INFO命令获取Redis详细信息（包含版本号）
            $info = $redis->info();
            // 3. 提取版本号（不同Redis版本key可能为redis_version或version）
            $version = $info['redis_version'] ?? $info['version'] ?? '未知版本';
            
            return $version;
        } catch (Exception $e) {
            return false;
        }
    }
}
