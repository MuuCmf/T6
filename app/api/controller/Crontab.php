<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Orders as OrdersModel;
use app\common\model\Evaluate as EvaluateModel;
use think\Exception;
use think\facade\Db;
use think\Request;

class Crontab extends Api
{
    private $OrdersModel; //订单模型

    function __construct(Request $request)
    {
        parent::__construct();
        $this->OrdersModel = new OrdersModel();
    }

    /**
     * 自动取消超时未支付订单
     * 
     * 查询24小时前创建且未支付的订单,将其状态更新为已取消
     * 每次处理最多100条订单记录,使用事务确保数据一致性
     * 
     * @return json 处理结果
     * @throws Exception 订单更新失败时抛出异常
     */
    public function ordersCancel()
    {
        $shopid = $this->shopid;
        Db::startTrans();
        try {
            //查询需要完成的订单
            $map = [
                ['status', '=', 1],
                ['paid', '=', 0],
                ['shopid', '=', $shopid],
                ['create_time', 'between', [0, time() - (24 * 60 * 60)]],
            ];
            $lists = $this->OrdersModel
                ->field('id,order_no,uid,products,status,create_time')
                ->where($map)
                ->limit(100) //并发限制
                ->select()
                ->toArray();

            if (!empty($lists)) {
                //更改订单状态
                $ids = array_column($lists, 'id');
                $data = [
                    'status'    =>  0, //已取消
                    'update_time' => time(),
                ];
                $result = $this->OrdersModel->where('id', 'in', $ids)->update($data);
                if ($result === false) throw new Exception('订单更新失败');
            }
            
            Db::commit();

            return json([
                'code' => 200,
                'msg' => 'success',
            ]);

        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code' => 0,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 订单自动评价
     * 处理超过7天未评价的已完成订单,自动添加默认好评
     * 
     * @return json 返回处理结果
     *              成功返回 code:200, msg:success
     *              失败返回 code:0, msg:错误信息
     */
    public function ordersEvaluate()
    {
        $shopid = $this->shopid;
        Db::startTrans();
        try {
            //查询需要完成的订单
            $map = [
                ['o.status', '=', 4],
                ['o.paid', '=', 1],
                ['o.shopid', '=', $shopid],
                ['o.update_time', 'between', [0,time() - (7 * 24 * 60 * 60)]],
            ];
            $lists = $this->OrdersModel
                          ->alias('o')
                          ->field('o.id,o.order_no,o.uid,products,CASE WHEN e.id IS NULL THEN 0 ELSE 1 END evaluate')
                          ->join('evaluate e','e.order_no = o.order_no')
                          ->where($map)
                          ->limit(100)//并发限制
                          ->select()
                          ->toArray();
            if (!empty($lists)){
                //写入评价
                $evaluate_data = [];
                foreach ($lists as $item){
                    if ($item['evaluate'] > 0){
                        $products = json_decode($item['products'],true);
                        $evaluate_data[] = [
                            'id'     => 'default',
                            'shopid' => $shopid,
                            'app' => 'minishop',
                            'uid' => $item['uid'],
                            'type' => 'goods',
                            'type_id' => $products['id'],
                            'order_no' => $item['order_no'],
                            'content' => '系统默认好评',
                            'images' => '',
                            'value' => 5.00,
                            'status' => 1
                        ];
                    }
                }
                $evaluate_result = (new EvaluateModel())->insertAll($evaluate_data);
                if ($evaluate_result === false) throw new Exception('评价失败');
                //更改订单状态
                $ids = array_column($lists,'id');
                $data = [
                    'status'    =>  5,//已评价
                    'update_time' => time(),
                    'end_time' => time()
                ];
                $result = $this->OrdersModel->where('id','in',$ids)->update($data);
                if ($result === false) throw new Exception('订单更新失败');
            }

            Db::commit();

            return json([
                'code' => 200,
                'msg' => 'success',
            ]);

        } catch (\Exception $e) {
            Db::rollback();
            return json([
                'code' => 0,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
