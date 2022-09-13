<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\History as HistoryModel;
use app\common\logic\History as HistoryLogic;

class History extends Api
{
    protected $model;
    protected $logic;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    function __construct()
    {
        parent::__construct();
        $this->HistoryLogic = new HistoryLogic();
        $this->HistoryModel = new HistoryModel();
        //添加jwt中间件
    }

    public function lists()
    {
        $uid = get_uid();
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1]
        ];

        $rows = 15;
        $order_field = input('order_field', 'update_time', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->HistoryModel->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->HistoryLogic->formatData($val);
        }
        unset($val);

        return $this->success('success',$lists);
    }

    /**
     * 记录数量
     */
    public function count()
    {
        $uid = get_uid();
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1]
        ];

        $count = $this->HistoryModel->where($map)->count();

        return $this->success('success', $count);
    }
}