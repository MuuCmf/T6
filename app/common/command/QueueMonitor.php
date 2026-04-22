<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 队列监控命令
 * 用于监控队列是否在运行，并定期更新心跳
 * 注意：此命令需要在后台运行，建议在服务器上启动。
 * 手动更新心跳（测试用）：
 * curl -X POST http http://your-domain/admin/queue/heartbeat
 * 如果需要自动监控，可以运行监控命令：
 * php think queue:monitor
 * 建议在启动队列工作进程时，同时运行监控命令：
 * 终端1：启动队列工作进程
 * php think queue:work --daemon
 * 终端2：启动监控
 * php think queue:monitor
 */
class QueueMonitor extends Command
{
    protected function configure()
    {
        $this->setName('queue:monitor')
            ->setDescription('监控队列状态并更新心跳');
    }

    protected function execute(Input $input, Output $output)
    {
        $config = config('queue.connections.redis');
        $queueName = config('queue.connections.redis.queue');
        $queueKey = '{queues:' . $queueName . '}';

        $output->writeln('开始监控队列: ' . $queueName);

        while (true) {
            try {
                $func = $config['persistent'] ? 'pconnect' : 'connect';
                $redis = new \Redis;
                $redis->$func($config['host'], $config['port'], $config['timeout']);

                if ('' != $config['password']) {
                    $redis->auth($config['password']);
                }

                if (0 != $config['select']) {
                    $redis->select($config['select']);
                }

                // 检查队列长度
                $queueLength = $redis->llen($queueKey);

                // 如果队列有任务，说明队列可能在运行
                if ($queueLength > 0) {
                    $heartbeatKey = 'queue:heartbeat:' . $queueName;
                    $redis->set($heartbeatKey, time());
                    $redis->expire($heartbeatKey, 600);
                    $output->writeln('[' . date('Y-m-d H:i:s') . '] 队列中有 ' . $queueLength . ' 个任务，已更新心跳');
                }

                $redis->close();
            } catch (\Exception $e) {
                $output->writeln('<error>[' . date('Y-m-d H:i:s') . '] 错误: ' . $e->getMessage() . '</error>');
            }

            // 每30秒检查一次
            sleep(30);
        }
    }
}
