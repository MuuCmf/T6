<?php
namespace app\ucenter\controller;

use think\Exception;
use think\facade\View;
use app\common\controller\Common;
use app\common\logic\Orders as OrdersLogic;
use app\common\model\Orders as OrdersModel;

/**
 * 订单页
 */
class Orders extends Common
{
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    private $OrdersModel;//订单模型
    private $OrdersLogic;//订单逻辑
    function __construct()
    {
        parent::__construct();
        $this->OrdersLogic = new OrdersLogic();
        $this->OrdersModel = new OrdersModel();
    }

    /**
     * @title 订单列表
     */
    public function lists()
    {
        $uid = get_uid();
        $status = input('status');
        $rows = input('rows', 15, 'intval');
        $status = input('status', 'all');
        $map = [
            ['shopid','=',$this->shopid],
            ['uid','=',$uid],
        ];

        if ($status  == 'all'){
            $map[] = ['status' ,'between' ,[0,9]];
        }else{
            $map[] = ['status' ,'=' ,$status];
        }

        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->OrdersModel->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->OrdersLogic->formatData($val);
        }
        unset($val);

        return View::fetch();
        
    }

    /**
     * @title 订单详情
     */
    public function detail()
    {
        $order_no = $this->params['order_no'];
        $order_data = $this->OrdersModel->getDataByOrderNo($order_no);
        $order_data = $this->OrdersLogic->formatData($order_data);

        // pc端商品路径
        $return_url = url($order_data['app'] . '/' . $order_data['products']['link']['url'], $order_data['products']['link']['param']);

        return View::fetch();
    }

}