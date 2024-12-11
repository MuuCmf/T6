<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Share as ShareModel;
use app\common\logic\Share as ShareLogic;

class Share extends Api
{
    protected $ShareModel;
    protected $ShareLogic;
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    function __construct()
    {
        parent::__construct();
        $this->ShareModel = new ShareModel();
        $this->ShareLogic = new ShareLogic();
    }

    /**
     * 获取分享列表
     * 
     * @param 无
     * @return array 分享列表数据
     * 
     * 该函数用于获取当前用户的分享列表，根据店铺ID、用户ID和状态进行筛选，并支持分页和排序。
     * 通过调用ShareModel的getListByPage方法获取数据，并使用ShareLogic的formatData方法格式化每条数据。
     * 最后，将格式化后的数据以成功的状态返回。
     */
    public function lists()
    {
        $uid = get_uid();
        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid],
            ['status', '=', 1]
        ];

        $rows = 15;
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->ShareModel->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach ($lists['data'] as &$val) {
            $val = $this->ShareLogic->formatData($val);
        }
        unset($val);

        return $this->success('success', $lists);
    }

    /**
     * 计算用户分享数量
     * 
     * @return array 成功状态和分享数量
     * 
     * 此方法用于计算当前用户在特定店铺下的有效分享数量。
     * 通过查询条件筛选出状态为1的分享记录，并统计数量。
     * 返回结果包含成功状态和分享数量。
     */
    public function count()
    {
        $uid = get_uid();
        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid],
            ['status', '=', 1]
        ];

        $count = $this->ShareModel->where($map)->count();

        return $this->success('success', $count);
    }
}
