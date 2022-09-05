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
        if (is_object($data)){
            $data = $data->toArray();
        }

        //订单状态
        $data['status_str'] = $this->_status[$data['status']];
        //售后退款状态
        $data['refund_str'] = $this->_refund[$data['refund']];
        //支付类型
        $data['channel_str'] = Channel::$_channel[$data['channel']];
        //买家信息
        $data['user_info'] = query_user($data['uid'],['nickname','avatar','mobile','email','create_time']);
        if (is_array($data['user_info'])){
            //格式化用户注册时间
            $data['user_info'] = $this->setTimeAttr($data['user_info']);
        }else{
            $data['user_info'] = [];
        }
        //订单架构
        if(isset($data['price'])){
            $data['price'] = sprintf("%.2f",$data['price']/100);
        }
        //实际支付金额
        if(isset($data['paid_fee'])) {
            $data['paid_fee'] = sprintf("%.2f",$data['paid_fee']/100);
        }
        //支付状态
        $data['paid_str'] = $this->_paid[$data['paid']];
        //支付时间
        $data = $this->setTimeAttr($data);
        //商品信息
        $data['products'] = json_decode($data['products'],true);
        $data['products']['cover_100'] = get_thumb_image($data['products']['cover'], 100,100);
        $data['products']['cover_200'] = get_thumb_image($data['products']['cover'], 200,200);
        $data['products']['cover_300'] = get_thumb_image($data['products']['cover'], 300,300);
        $data['products']['cover_400'] = get_thumb_image($data['products']['cover'], 400,400);
        $data['products']['cover_800'] = get_thumb_image($data['products']['cover'], 800,800);
        $data['products']['price'] = sprintf("%.2f",$data['products']['price']/100); //商品单价
        $data['products']['type'] = $data['products']['type'] ?? 0;
        //地址
        $data['address_info'] = $data['address_id'] ? (new \app\common\model\Address())->getDataById($data['address_id']) : [];
        //物流数据
        $data['logistic'] = json_decode($data['logistic'],true);
        //是否已评价,已评价获取评价内容，未评价值为0
        if($data['paid'] == 1){
            $evaluate_map = [];
            $evaluate_map[] = ['uid','=',$data['uid']];
            $evaluate_map[] = ['order_no','=',$data['order_no']];
            $evaluate_map[] = ['shopid','=',$data['shopid']];
            $data['evaluate'] = (new \app\common\model\Evaluate())->getThisEvaluate($evaluate_map);
        }else{
            $data['evaluate'] = 0;
        }
        return $data;
    }
}