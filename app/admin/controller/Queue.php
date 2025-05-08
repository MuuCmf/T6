<?php
namespace app\admin\controller;

use think\facade\View;
use think\paginator\driver\Bootstrap;

/**
 * 消息队列控制器
 */
class Queue extends Admin {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取队列列表
     * 
     * 从Redis队列中获取分页数据,包含以下功能:
     * - 连接Redis服务器
     * - 根据页码获取指定范围的队列数据
     * - 对队列数据进行JSON解码
     * - 组装分页数据结构
     * - 生成分页导航
     * 
     * @return mixed 返回渲染后的视图
     */
    public function lists()
    {
        // 连接redis
        $redis = $this->connRedis();

        $key = '{queues:' .config('queue.connections.redis.queue'). '}';
        $page = input('page', 1, 'intval');
        $page_size = 10;
        
        // 起始索引
        $limit_s = ($page-1) * $page_size;
        // 结束索引
        $limit_e = ($limit_s + $page_size) - 1;
        $page_list = $redis->lrange($key, $limit_s,$limit_e); //指定区间内列表。
        $count = $redis->llen($key);
        $page_count = intval(ceil($count/$page_size)); //总共多少页
        foreach($page_list as &$v){
            $v = json_decode($v, true);
            $v['json'] = json_encode($v);
        }
        unset($v);

        // 组装数据
        $lists = [
            'current_page' => $page,
            'last_page' => $page_count,
            'per_page' => $page_size,
            'total' => $count,
            'data'=> $page_list
        ];

        // 分页
        $pager = (new Bootstrap($page_list, $page_size, $page, $count, false, [
            'path' => '/admin/queue/lists',
            'var_page' => 'page'
        ]))->render();
        
        $this->setTitle('消息队列');
        View::assign('pager', $pager);
        View::assign('lists', $lists);
        //dump($lists);
        // 输出模板
        return View::fetch();
    }

    /**
     * 连接redis
     */
    private function connRedis()
    {
        // 连接redis
        $config = config('queue.connections.redis');
        $func   = $config['persistent'] ? 'pconnect' : 'connect';

        $client = new \Redis;
        $client->$func($config['host'], $config['port'], $config['timeout']);

        if ('' != $config['password']) {
            $client->auth($config['password']);
        }

        if (0 != $config['select']) {
            $client->select($config['select']);
        }

        return $client;
    }

}