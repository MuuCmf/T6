<?php
namespace app\common\listener;

use think\queue\event\JobProcessing;
use think\queue\event\JobProcessed;

/**
 * 队列心跳监听器
 * 当队列处理任务时自动更新心跳时间戳
 */
class QueueHeartbeatListener
{
    /**
     * 更新心跳
     */
    protected function updateHeartbeat()
    {
        try {
            $queueName = config('queue.connections.redis.queue');
            $config = config('queue.connections.redis');

            $func = $config['persistent'] ? 'pconnect' : 'connect';
            $redis = new \Redis;
            $redis->$func($config['host'], $config['port'], $config['timeout']);

            if ('' != $config['password']) {
                $redis->auth($config['password']);
            }

            if (0 != $config['select']) {
                $redis->select($config['select']);
            }

            $heartbeatKey = 'queue:heartbeat:' . $queueName;
            $redis->set($heartbeatKey, time());
            $redis->expire($heartbeatKey, 600); // 10分钟过期

            $redis->close();
        } catch (\Exception $e) {
            // 心跳更新失败不影响任务执行
        }
    }

    /**
     * 任务开始处理时更新心跳
     */
    public function handle(JobProcessing $event)
    {
        $this->updateHeartbeat();
    }

    /**
     * 任务处理完成后更新心跳
     */
    public function handleProcessed(JobProcessed $event)
    {
        $this->updateHeartbeat();
    }
}
