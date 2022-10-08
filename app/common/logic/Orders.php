<?php
namespace app\common\logic;

/*
 * MuuCmf
 * 订单数据逻辑层
 */

use app\channel\logic\Channel;

class Orders extends Base
{
    public $shipper = [
        'SF'=>'顺丰快递',
        'HTKY'=>'百世快递',
        'ZTO'=>'中通快递',
        'STO'=>'申通快递',
        'YTO'=>'圆通速递',
        'YD'=>'韵达速递',
        'YZPY'=>'邮政快递包裹',
        'EMS'=>'EMS',
        'HHTT'=>'天天快递',
        'JD'=>'京东快递'
    ];

    /**
     * 订单通用状态
     * @var string[]
     */
    public $_status = [
        1 => '待付款',
        2 => '待发货', 
        3 => '待收货', 
        4 => '已收货', //确认收货
        5 => '已完成', //已完成评价
        0 => '已取消',
        -1 => '已删除'
    ];

    /**
     * 支付状态
     * @var string[]
     */
    protected $_paid = [
        0 => '未支付',
        1 => '已支付'
    ];

    /**
     * 退款状态
     * @var string[]
     */
    public $_refund = [
        -1 => '拒绝退款',
        0 => '未申请退款',
        1 => '退款申请中',
        2 => '退货中',
        3 => '已退货',
        4 => '退款完成',
        //5 => '已完成',
    ];

    /**
     * 支付渠道
     * @var [type]
     */
    public $_pay_channel = [
        'weixin' => '微信',
        'alipay' => '支付宝',
        'offline' => '线下支付',
        'score' => '积分',
        'convert' => '兑换码',
        '' => '无'
    ];

    /**
     * 格式化数据
     */
    public function formatData($data)
    {
        $order_namespace = "app\\{$data['app']}\\logic\\Orders";
        $appOrdersLogic = new $order_namespace;
        
        $data = $appOrdersLogic->formatData($data);
        
        return $data;
    }
}