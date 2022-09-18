<?php
namespace app\channel\service\bytedance;

use app\channel\model\DouyinMpConfig;
use app\common\model\Orders as OrdersModel;
use app\common\logic\Orders as OrdersLogic;
use think\Exception;

class DouyinMp
{
    private $title;
    private $appid;
    private $secret;
    private $token;
    private $salt;
    private $api;

    /**
     * 构造配置项
     **/
    public function __construct()
    {
        $this->api = 'https://developer.toutiao.com';
        //服务配置文件
        $config = $this->config = $this->initConfig();

        $this->title = $config['title'];
        $this->appid = $config['appid'];
        $this->secret = $config['secret'];
        $this->salt = $config['salt'];
        $this->token = $config['token'];
    }

    public function initConfig()
    {
        $this->shopid = request()->param('shopid') ?? 0;
        //获取配置信息
        $map = [
            ['shopid' ,'=' ,$this->shopid],
        ];
        $data = (new DouyinMpConfig())->where($map)->find();
        if (empty($data)){
            throw  new Exception('小程序配置信息不存在');
        }
        $data = $data->toArray();
        return [
            'title' => $data['title'],
            'appid' => $data['appid'],
            'secret' => $data['secret'],
            'token' => $data['token'],
            'salt' => $data['salt']
        ];
    }

    /**
     * 获取access_token
     **/
    public function getAccessToken()
    {
        $params = [
            'appid' => $this->appid,
            'secret' => $this->secret,
            'grant_type' => 'client_credential'
        ];

        $result = $this->sendPost('/api/apps/v2/token',$params);
        $result = json_decode($result, true);
        if($result['err_no'] == 0){
            return $result['data']['access_token'];
        }

        return false;
    }

    /**
     * 获取session_key 和 openId。
     */
    public function code2Session($code, $anonymous_code)
    {
        $params = [
            'appid' => $this->appid,
            'secret' => $this->secret,
            'code' => $code,
            'anonymous_code' => $anonymous_code
        ];

        $access_token = $this->accessToken = $this->getAccessToken();
        if($access_token){
            $result = $this->sendPost('/api/apps/v2/jscode2session?access_token=' . $access_token, $params);
            
            return json_decode($result, true);
        }

        return false;
        
    }

    /**
     * 预下单接口
     */
    public function createOrder($params)
    {
        $params = $params;
        $params['app_id'] = $this->appid;
        $params['valid_time'] = 172800;
        $params['sign'] = $this->sign($params);

        $access_token = $this->accessToken = $this->getAccessToken();
        if($access_token){
            $result = $this->sendPost('/api/apps/ecpay/v1/create_order?access_token=' . $access_token, $params);
            
            return json_decode($result, true);
        }
    }

    /**
     * 订单推送
     */
    public function ordersPush($order_no)
    {
        // 获取订单数据
        $order_info = (new OrdersModel)->getDataByOrderNo($order_no);
        $products = json_decode($order_info['products'], true);
        // 获取用户openid
        $open_id = get_openid($this->shopid, $order_info['uid'] ,'douyin_mp');

        // 订单状态
        $status = $order_info['status'];
        if($status == 1){
            $status_str = '待支付';
            $order_status = 0;
        } 
        if($status == 2){
            $status_str = '已支付';
            $order_status = 1;
        } 
        if($status == 4 || $status == 5){
            $status_str = '已核销';
            $order_status = 4;
        } 
        if($status == 0){
            $status_str = '已取消';
            $order_status = 2;
        } 
        if($order_info['refund'] == 1 || $order_info['refund'] == 2 || $order_info['refund'] == 3){
            $status_str = '退款中';
            $order_status = 5;
        }
        if($order_info['refund'] == 4){
            $status_str = '已退款';
            $order_status = 6;
        }
        // 商品数量
        $quantity = 1;
        if(isset($products['quantity'])){
            $quantity = $products['quantity'];
        }

        // 子订单列表
        $item_list = [
            [
                'item_code' => $order_info['app'] .'_'. $order_info['order_info_type'] .'_'. $order_info['order_info_id'],
                'img' => get_thumb_image($products['cover'], 400, 400),
                'title' => $products['title'],
                'sub_title' => $products['description'],
                'amount' => intval($quantity),
                'price' => intval(($products['price'] * $quantity) * 100)
            ]
        ];
        // 订单详情
        $order_detail = [
            'order_id' => $order_no,
            'create_time' => intval($order_info['create_time'] . '000'),
            'status' => $status_str,
            'amount' => intval($quantity),
            'total_price' => intval($order_info['paid_fee']),
            'detail_url' => $order_info['app'] . '/' . $products['link']['url'] .'?'. http_build_query($products['link']['param']),
            'item_list' => $item_list
        ];

        // 组装请求数据
        $params['app_name'] = 'douyin';
        $params['open_id'] = $open_id;
        $params['order_detail'] = json_encode($order_detail);
        $params['order_status'] = intval($order_status);
        $params['order_type'] = 0;
        $params['update_time'] = intval($this->getMillisecond());
        $params['access_token'] = $this->getAccessToken();

        $result = $this->sendPost('/api/apps/order/v2/push',$params);
        return json_decode($result, true);
    }

    public function post($url, $data = [], $second = 30, $header = [])
    {
        $curl = curl_init();

        if (stripos($url, "https") === 0) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, $second);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        list($content, $status) = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        var_dump($content);
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 生成二维码
     */
    public function createQRCode($path)
    {
        $params = [
            'access_token' => $this->getAccessToken(),
            'appname' => 'douyin',
            'path' => $path,
        ];

        return $this->sendPost('/api/apps/qrcode',$params);
    }

    /**
     * 请求签名
     */
    public function sign($map)
    {
        $rList = array();
        foreach($map as $k =>$v) {
            if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0,1)=="\"" && substr($value,$len, $len-1)=="\"")
                $value = substr($value,1, $len-1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        array_push($rList, $this->salt);
        sort($rList, 2);
        return md5(implode('&', $rList));
    }

    /**
     * 回调验签
     * @param array $map 验签参数
     * @return stirng
    */
    public function handler($map){
        $rList = array();
        array_push($rList, $this->token);
        foreach($map as $k =>$v) {
            if ( $k == "type" || $k=='msg_signature')
                continue;
            $value = trim(strval($v));
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        sort($rList,2);
        return sha1(implode($rList));
    }

    /**
     * post请求
     **/
    private function sendPost($url,$data)
    {
        $post_data = json_encode($data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/json',
                'content' => $post_data,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->api.$url, false, $context);
        return $result;
    }

    /**
     * 13位时间戳
     */
    private function getMillisecond() { 
        list($t1, $t2) = explode(' ', microtime()); 
        return (int)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000); 
    } 
}