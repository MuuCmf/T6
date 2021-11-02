<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Wechat.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/10/27
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\service\pay;
use app\common\model\Orders;
use app\unions\model\MiniProgramConfig;
use EasyWeChat\Factory;
use think\Exception;

class WechatPayment extends PayService{

    function __construct($appid)
    {
        $this->type = 'wechat';
        //服务配置文件
        $config = $this->config =  $this->initConfig($appid);
        $app =  Factory::payment($config);
        parent::__construct($app);
    }
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
            'cert_path' => app()->getRootPath() . 'public/cert/wechat_cert.pem',
            'key_path' => app()->getRootPath() . 'public/cert/wechat_key.pem',
            'notify_url' => request()->domain() . "/union/PayService/callback",
            'sandbox' => $this->sandbox,//沙盒模式开关
        ];
    }

    public function pay($data)
    {
        // TODO: Implement pay() method.
        $pay_data = [];
        $pay_data['body'] = $data['title'];
        $pay_data['out_trade_no'] = $data['order_no'];
        $pay_data['total_fee'] = intval($data['price']);
        $pay_data['trade_type'] = $data['trade_type'] ?? 'JSAPI';
        $pay_data['openid'] = $data['openid'];
        if (isset($data['notify_url'])){
            $pay_data['notify_url'] = $data['notify_url'];
        }
        $res = $this->app->order->unify($pay_data);
        $res = $this->app->jssdk->sdkConfig($res['prepay_id']);
        return $res;
    }
    
    public function refund($order)
    {
        // TODO: Implement refund() method.
        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        return $this->app->refund->byOutTradeNumber($order['order_no'], $order['refund_no'], $order['price'], $order['config'] ?? []);

    }
    public function notify($params)
    {
        // TODO: Implement notify() method.
        if($params['return_code'] == 'SUCCESS' && $params['result_code'] == 'SUCCESS'){
            //查询订单是否已支付
            $map = [];
            $map[] = ['paid','=',1];
            $map[] = ['order_no','=',$params['out_trade_no']];
            $count = Orders::where($map)->count();
            if ($count > 0){
                return  false;
            }
            return true;
        }
        return false;
    }
}