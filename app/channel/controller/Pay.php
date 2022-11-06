<?php
namespace app\channel\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\logic\Orders as OrdersLogic;
use \app\common\model\Orders as OrdersModel;

/**
 * PC端统一支付类
 */
class Pay extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 微信扫码支付
     */
    public function weixin()
    {
        $order_no = input('order_no', '', 'text');
        View::assign('order_no', $order_no);
        // 获取订单数据
        $order_data = (new OrdersModel)->getDataByOrderNo($order_no);
        $order_data = (new OrdersLogic)->formatData($order_data);
        View::assign('order_data', $order_data);
        // 二维码路径
        $code_url = input('code_url', '', 'text');
        $code_url = url('api/qrcode/create', ['url' => $code_url]);
        View::assign('code_url', $code_url);

        // 输出模板
        return View::fetch();
    }
}