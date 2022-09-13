<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Lib\Timer;
use Workerman\Worker;
use app\common\model\Crontab as CrontabModel;

class Crontab extends Command{
    protected $CrontabModel;//计划任务模型

    public function __construct()
    {
        parent::__construct();
        $this->CrontabModel = new CrontabModel();
    }

    protected function configure()
    {
        // 指令配置 php think crontab start --d 守护进程启动
        $this->setName('crontab')
            ->addArgument('status', Argument::REQUIRED, 'start/stop/reload/status/connections')
            ->addOption('d', null, Option::VALUE_NONE, 'daemon（守护进程）方式启动')
            ->addOption('i', null, Option::VALUE_OPTIONAL, '多长时间执行一次')
            ->setDescription('开启/关闭/重启 定时任务');
    }
    protected function init(Input $input, Output $output)
    {
        global $argv;
        if ($input->hasOption('i'))
            $this->interval = floatval($input->getOption('i'));
        $argv[1] = $input->getArgument('status') ?: 'start';
        if ($input->hasOption('d')) {
            $argv[2] = '-d';
        } else {
            unset($argv[2]);
        }
    }

    protected function execute(Input $input, Output $output)
    {

        $this->init($input, $output);
        //创建定时器任务
        $task = new Worker();
        $task->count = 1;
        $task->onWorkerStart = [$this, 'start'];
        $task->runAll();
    }

    /**
     * @title 开启定时任务
     */
    public function start()
    {
        //查询任务队列
        $map = [
            ['status','=',1]
        ];
        $task_list = $this->CrontabModel->where($map)->field('id,shopid,execute,cycle,day,hour,minute')->select()->toArray();
        foreach ($task_list as $index => $task){
            //格式化天
            $d = $task['day'];
            //格式化小时
            $h = $task['hour'];
            //格式化分钟
            $i = $task['minute'];

            switch ($task['cycle']){
                case 'month'://每月执行
                    $rule = "{$i} {$h} {$d} * *";
                    break;
                case 'day'://每天执行
                    $rule = "{$i} {$h} * * *";
                    break;
                case 'hour'://每小时执行
                    $rule = "{$i} * * * *";
                    break;
                default:
                    //N段时间执行
                    switch ($task['cycle']){
                        case 'day-n'://n天执行
                            $time_interval = ($d * 24 * 60 *60) + ($h * 60 * 60) + (60 * $i);
                            break;
                        case 'hour-n'://n小时执行
                            $time_interval = ($h * 60 * 60) + (60 * $i);
                            break;
                        case 'minute-n'://n分钟执行
                            $time_interval = $i * 60;
                            break;
                        default:
                            $time_interval = 60;
                            break;
                    }
                    Timer::add($time_interval, function () use ($task){
                        (new $task['execute'])->handle($task['shopid'],$task['id']);//处理任务
                    });
                    $rule = false;
                    break;
            }
            //完整日期任务
            if ($rule){
                //加载任务
                new \Workerman\Crontab\Crontab($rule, function() use ($task){
                    (new $task['execute'])->handle($task['shopid'],$task['id']);//处理任务
                });
            }
        }
    }

    public function stop()
    {
        //手动暂停定时器
        Worker::stopAll();
    }

}