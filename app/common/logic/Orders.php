<?php

namespace app\common\logic;

/*
 * MuuCmf
 * 订单数据逻辑层
 */
class Orders extends Base
{
    public $shipper = [
        'SF' => '顺丰快递',
        'HTKY' => '百世快递',
        'ZTO' => '中通快递',
        'STO' => '申通快递',
        'YTO' => '圆通速递',
        'YD' => '韵达速递',
        'YZPY' => '邮政快递包裹',
        'EMS' => 'EMS',
        'HHTT' => '天天快递',
        'JD' => '京东快递'
    ];

    /**
     * 订单通用状态
     * @var string[]
     */
    public $_status = [
        1 => '待付款',
        2 => '待发货',
        3 => '待收货',
        4 => '待评价', //确认收货
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
        'douyin' => '抖音',
        'baidu'  => '百度',
        'offline' => '线下支付',
        'score' => '积分',
        'convert' => '兑换码',
        'password' => '密码',
        'admin' => '后台手动设置',
        '' => '未知'
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

    /**
     * 导出数据格式化
     */
    public function exportParse($list, $header = array())
    {
        if (empty($list)) {
            return '';
        }

        $keys = array_keys($header);
        $html = "\xEF\xBB\xBF";
        foreach ($header as $li) {
            $html .= $li . "\t ,";
        }
        $html .= "\n";
        $count = count($list);
        $pagesize = ceil($count / 5000);
        for ($j = 1; $j <= $pagesize; $j++) {
            $list = array_slice($list, ($j - 1) * 5000, 5000);

            if (!empty($list)) {
                $size = ceil(count($list) / 500);
                for ($i = 0; $i < $size; $i++) {
                    $buffer = array_slice($list, $i * 500, 500);

                    $column_data = array();
                    foreach ($buffer as &$row) {
                        if ($row)
                            if ($row['paid'] == 1) {
                                $row['paid_time'] = date('Y-m-d H:i:s', $row['paid_time']);
                            }
                        $row['paid'] = $this->_paid[$row['paid']];
                        $row['paid_fee'] = '￥' . sprintf("%.2f", $row['paid_fee'] / 100);
                        $row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
                        $row['products'] = json_decode($row['products'], true);
                        $row['products_title'] = $row['products']['title'];
                        foreach ($keys as $key) {
                            $data[] = $row[$key];
                        }
                        $column_data[] = implode("\t ,", $data) . "\t ,";
                        unset($data);
                    }
                    unset($row);
                    $html .= implode("\n", $column_data) . "\n";
                }
            }
        }
        return $html;
    }
}
