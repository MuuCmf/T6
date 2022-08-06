<?php
namespace app\common\service;

use think\Exception;
use app\common\model\Orders as OrdersModel;
use app\common\logic\Orders as OrdersLogic;
use app\common\model\Member;
use app\common\model\VipCard as VipCardModel;
use app\common\model\Vip as VipModel;

/*
 * VIP会员订单数据
 */
class VipOrders extends OrdersLogic
{
    protected $OrdersModel;

    public function __construct()
    {
        $this->OrdersModel = new OrdersModel();
    }

    /**
     * 统一下单 只需处理应用自身业务逻辑
     * @param $params
     */
    public function create($params = [])
    {
        $shopid = intval($params['shopid']);
        $uid = intval($params['uid']);
        $order_info_id = intval($params['order_info_id']);//ID
        $order_info_type = $params['order_info_type'];
        
        //获取商品信息
        //VIP卡项
        $product_data = (new VipCardModel)->getDataById($order_info_id);
        $type = $order_info_type;
        $type_str = 'Vip卡';
        $cycle = $params['cycle'];
        //单价
        $price = intval($product_data[$cycle . '_price']);
        //计算应付金额
        $paid_fee = intval($product_data[$cycle . '_price']); //实际支付金额
        
        if (!$product_data){
            throw new Exception('数据不存在');
        }

        //判断内容是否上架
        if($product_data['status'] != 1){
            throw new Exception('内容已下架或删除');
        }

        //组装写入订单表数据
        $products = [
            'id' => $product_data['id'],
            'title' => $product_data['title'],
            'type' => $type, // 内容类型标识
            'type_str' => $type_str,
            'cycle' => $cycle,
            'description' => $product_data['description'],
            'price' => $price, //商品单价
            'cover' => $product_data['cover'],
            'link' => [
                'url' => $order_info_type . '/detail',
                'param' => [
                    'id' => $product_data['id']
                ]
            ]
        ];
        $products = json_encode($products);
        $paid = 0;
        $status = 1;
        $paid_time = 0;
        
        //有效期
        // 查询会员状态
        $vip_data = (new VipModel)->where([
            ['app', '=', $product_data['app']],
            ['shopid', '=', $shopid],
            ['uid', '=', $uid],
            ['card_id', '=', $product_data['id']],
            ['status', 'in', [0,1]]
        ])->find();
        
        //未过期会员处理
        if(!empty($vip_data)){
            // 未过期会员续费
            if($vip_data['end_time'] > time()){
                switch ($cycle){
                    case 'month':
                    $time = strtotime('+1 month',$vip_data['end_time']);
                    break;
                    case 'quarter':
                    $time = strtotime('+3 month',$vip_data['end_time']);
                    break;
                    case 'year':
                    $time = strtotime('+1 year',$vip_data['end_time']);
                    break;
                }
            }else{
                //已过期用户
                switch ($cycle){
                    case 'month':
                    $time = strtotime('+1 month');
                    break;
                    case 'quarter':
                    $time = strtotime('+3 month');
                    break;
                    case 'year':
                    $time = strtotime('+1 year');
                    break;
                }
            }
            
        }else{
            switch ($cycle){
                case 'month':
                $time = strtotime('+1 month');
                break;
                case 'quarter':
                $time = strtotime('+3 month');
                break;
                case 'year':
                $time = strtotime('+1 year');
                break;
            }
        }
        //订单内有效期
        $end_time = $time;

        //来源渠道
        $channel = $params['channel'];

        //组装提交数据
        $data['app'] = $params['app'];
        $data['shopid'] = $shopid;
        $data['order_no'] = build_order_no();
        $data['uid'] = $uid;
        $data['paid'] = $paid;
        $data['paid_fee'] = $paid_fee;
        $data['paid_time'] = $paid_time;
        $data['type'] = $cycle;
        $data['delivery_fee'] = 0;
        $data['channel'] = $channel;
        $data['pay_channel'] = '';
        $data['address_id'] = 0;
        $data['products'] = $products;
        $data['status'] = $status;
        $data['price'] = $price;
        $data['order_info_id'] = $order_info_id;
        $data['order_info_type'] = $order_info_type;
        $data['remark'] = $params['remark'] ?? '';
        $data['receipt'] = $params['receipt'] ?? '';
        $data['end_time'] = $end_time;

        return $data;
    }

    /**
     * 支付成功后业务处理(约定方法名)
     */
    public function paySuccess($order_info)
    {
        //返回值
        $result = [];
        //获取订单数据，处理后续业务逻辑
        $order_info['products'] = json_decode($order_info['products'], true);
        $data = [
            'id' => $order_info['id'],
            'paid' => 1,
            'paid_time' => time(),
            'status' => 5
        ];
        //更改自身业务系统订单状态为已支付
        $this->OrdersModel->edit($data);
        //后续处理
        $this->step($order_info);
        //返回
        $result['order_info'] = $order_info;

        return $result;
    }

    /**
     * 支付成功后续处理
     */
    public function step($order_info)
    {
        $shopid = $order_info['shopid'];
        $info_id = $order_info['order_info_id'];
        // 获取卡项数据
        $vip_card_data = (new VipCardModel)->where('id', $info_id)->find();
        $vip_card_data = $vip_card_data->toArray();
        $app = $vip_card_data['app'];
        $uid = $order_info['uid'];
        $cycle = $order_info['products']['cycle'];
        // 获取会员数据
        $vip_data = (new VipModel)->where([
            ['app', '=', $app],
            ['shopid', '=', $shopid],
            ['uid', '=', $uid],
            ['card_id', '=', $vip_card_data['id']],
            ['status', 'in', [0,1]]
        ])->find();
        
        //未过期会员处理
        if(!empty($vip_data)){
            // 未过期会员续费
            $vip_data_id = $vip_data['id'];
            if($vip_data['end_time'] > time()){
                switch ($cycle){
                    case 'month':
                    $time = strtotime('+1 month',$vip_data['end_time']);
                    break;
                    case 'quarter':
                    $time = strtotime('+3 month',$vip_data['end_time']);
                    break;
                    case 'year':
                    $time = strtotime('+1 year',$vip_data['end_time']);
                    break;
                }
            }else{
                //已过期用户
                switch ($cycle){
                    case 'month':
                    $time = strtotime('+1 month');
                    break;
                    case 'quarter':
                    $time = strtotime('+3 month');
                    break;
                    case 'year':
                    $time = strtotime('+1 year');
                    break;
                }
            }
            
        }else{
            $vip_data_id = 0;
            switch ($cycle){
                case 'month':
                $time = strtotime('+1 month');
                break;
                case 'quarter':
                $time = strtotime('+3 month');
                break;
                case 'year':
                $time = strtotime('+1 year');
                break;
            }
        }
        //订单内有效期
        $end_time = $time;
        // 更新VIP会员数据
        (new VipModel)->edit([
            'id' => $vip_data_id,
            'app' => $app,
            'uid' => $uid,
            'card_id' => $info_id,
            'order_no' => $order_info['order_no'],
            'end_time' => $end_time,
            'status' => 1
        ]);

        return $order_info;
    }

}