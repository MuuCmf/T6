<?php
namespace app\channel\service\pay;

use app\common\model\Orders;
use app\channel\model\WechatMpConfig;
use EasyWeChat\Factory;
use think\Exception;

class WechatPayment extends PayService
{
    function __construct($appid)
    {
        $this->type = 'wechat';
        //服务配置文件
        $config = $this->config =  $this->initConfig($appid);
        $app =  Factory::payment($config);
        parent::__construct($app);
    }

    /**
     * 初始化配置
     */
    public function initConfig($appid)
    {
        //获取配置信息
        $mchid = config('extend.WX_PAY_MCH_ID');
        $key = config('extend.WX_PAY_KEY_SECRET');
        if (empty($mchid)){
            throw new Exception('请填写商户ID');
        }
        if (empty($key)){
            throw new Exception('请填写商户密钥');
        }
        return [
            'app_id' => $appid,
            'mch_id' => $mchid,
            'key' => $key,
            'cert_path' => app()->getRootPath() . 'public/attachment/' . config('extend.WX_PAY_CERT'),
            'key_path' => app()->getRootPath() . 'public/attachment/' . config('extend.WX_PAY_KEY'),
            'notify_url' => request()->domain() . "/api/pay/callback",
            'sandbox' => $this->sandbox,//沙盒模式开关
        ];
    }


    /**
     * 支付
     * @param $data 数据
     * @param string $trade_type 支付类型
     * @param string $notify_url 回调
     * @return mixed
     */
    public function pay($data ,$trade_type = 'JSAPI')
    {
        // TODO: Implement pay() method.
        $data['trade_type'] = $trade_type;
        if (!empty($notify_url)){
            $data['notify_url'] = $notify_url;
        }
        $res = $this->app->order->unify($data);
        if ($res['return_code'] == 'FAIL'){
            throw new Exception($res['return_msg']);
        }
        $res = $this->app->jssdk->sdkConfig($res['prepay_id']);
        return $res;
    }

    /**
     * @title 退款
     * @param $refund_info
     * @return bool
     * @throws Exception
     */
    public function refund($refund_info)
    {
        // TODO: Implement refund() method.
        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        $result = $this->app->refund->byOutTradeNumber($refund_info['order_no'], $refund_info['refund_no'], $refund_info['total_fee'],$refund_info['refund_fee'], [
            'refund_desc' => $refund_info['title']
        ]);
        //if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
        return $result;
        //}
    }

    /**.
     * @title 回调
     * @param $params
     * @return bool
     */
    public function notify($params)
    {
        // TODO: Implement notify() method.
        if($params['return_code'] == 'SUCCESS' && $params['result_code'] == 'SUCCESS'){
            return $params['out_trade_no'];
        }
        return false;
    }

    /**
     * @title 商户订单号查询订单
     * @param $order_no
     * @return mixed
     */
    public function queryByOutTradeNumber($order_no){
        return $this->app->order->queryByOutTradeNumber($order_no);
    }

    /**
     * @title 企业付款到零钱
     * @param $data
     * @return mixed
     */
    public function toBalance($data){
//        $data = [
//            'partner_trade_no' => '1233455', // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
//            'openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
//            'check_name' => 'FORCE_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
//            're_user_name' => '王小帅', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
//            'amount' => 10000, // 企业付款金额，单位为分
//            'desc' => '理赔', // 企业付款操作说明信息。必填
//        ];
        return $this->app->transfer->toBalance($data);

    }
}