<?php
namespace app\channel\service\bytedance;

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
        $this->api = 'https://open-sandbox.douyin.com';
        $this->appid = '';
        $this->secret = '';
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