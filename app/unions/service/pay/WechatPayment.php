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
use EasyWeChat\Factory;
use think\Exception;

class WechatPayment extends PayService{

    function __construct()
    {
        $this->type = 'wechat';
        //服务配置文件
        $config = $this->config =  $this->initConfig();
        $app =  Factory::payment($config);
        parent::__construct($app);
    }
    public function initConfig()
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
        //登录信息中，保存当前应用appid
        $appid = [];
        $appid = 'wx90fcefad8616a371';
        return [
            'app_id' => $appid,
            'mch_id' => $mchid,
            'key' => $key,
            'cert_path' => app()->getRootPath() . 'public/cert/wechat_cert.pem',
            'key_path' => app()->getRootPath() . 'public/cert/wechat_key.pem',
            'notify_url' => request()->domain() . "/api/PayService/callback",
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
    
    public function refund()
    {
        // TODO: Implement refund() method.
    }
}