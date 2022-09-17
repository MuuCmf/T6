<?php
namespace app\channel\service\bytedance;

use app\channel\model\DouyinMpConfig;
use think\Exception;

class DouyinMp
{
    private $appid;
    private $secret;
    private $api;

    /**
     * 构造配置项
     **/
    public function __construct()
    {
        $this->api = 'https://developer.toutiao.com';
        //服务配置文件
        $config = $this->config = $this->initConfig();

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
}