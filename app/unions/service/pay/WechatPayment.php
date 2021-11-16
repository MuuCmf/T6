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
            'notify_url' => request()->domain() . "/api/pay/payCallback",
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
            return $params['out_trade_no'];
        }
        return false;
    }
    public function queryByOutTradeNumber($order_no){
        return $this->app->order->queryByOutTradeNumber($order_no);
    }
}