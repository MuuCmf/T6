<?php
namespace app\channel\service\baidu;

use muucmf\Rsa;
use app\channel\model\BaiduMpConfig;
use think\Exception;

class BaiduMp
{
    private $shopid;
    private $title;
    private $description;
    private $appid;
    private $appkey;
    private $secret;
    private $pay_appid;
    private $pay_appkey;
    private $dealId;
    private $rsa_public_key;
    private $rsa_private_key;

    /**
     * 构造配置项
     **/
    public function __construct()
    {
        //服务配置文件
        $config = $this->initConfig();

        $this->title             = $config['title'];
        $this->description       = $config['description'];
        $this->appid             = $config['appid'];
        $this->appkey            = $config['appkey'];
        $this->secret            = $config['secret'];
        $this->pay_appid         = $config['pay_appid'];
        $this->pay_appkey        = $config['pay_appkey'];
        $this->dealId            = $config['dealId'];
        $this->rsa_public_key    = $config['rsa_public_key'];
        $this->rsa_private_key   = $config['rsa_private_key'];
    }

    public function initConfig()
    {
        $this->shopid = request()->param('shopid') ?? 0;
        //获取配置信息
        $map = [
            ['shopid' ,'=' ,$this->shopid],
        ];
        $data = (new BaiduMpConfig())->where($map)->find();
        if (empty($data)){
            throw  new Exception('小程序配置信息不存在');
        }
        $data = $data->toArray();
        return $data;
    }

    /**
     * 预下单获取orderInfo接口
     */
    public function createOrder($params)
    {
        $result['dealId'] = $this->dealId;
        $result['appKey'] = $this->pay_appkey;
        $result['totalAmount'] = (string)$params['totalAmount'];
        $result['tpOrderId'] = $params['tpOrderId'];
        $result['notifyUrl'] = $params['notifyUrl'];
        $result['dealTitle'] = $params['dealTitle'];
        $result['signFieldsRange'] = '1'; // 用于区分验签字段范围，signFieldsRange 的值：0：原验签字段 appKey+dealId+tpOrderId；1：包含 totalAmount 的验签，验签字段包括appKey+dealId+tpOrderId+totalAmount。固定值为 1 。
        // 生成签名
        $assocArr = [
            'appKey' => $result['appKey'],
            'dealId' => $result['dealId'],
            'tpOrderId' => $result['tpOrderId'],
            'totalAmount' => $result['totalAmount']
        ];

        // 参数按字典顺序排序
        ksort($assocArr); 

        $parts = [];
        foreach ($assocArr as $k => $v) {
            $parts[] = $k . '=' . $v;
        }
        $str = implode('&', $parts);

        $rsaSign = (new Rsa(null, $this->rsa_private_key))->sign($str);
        $result['rsaSign'] = $rsaSign;
        $result['bizInfo'] = '';
        $result['payResultUrl'] = '';
        $result['inlinePaySign'] = '';
        $result['promotionTag'] = '';

        return $result;
        
    }

    /**
     * 生成二维码
     */
    public function createQRCode($path)
    {
        
    }

    /**
     * 回调验签
     * @param array $map 验签参数
     * @return stirng
    */
    public function checkSign($assocArr){
        if (empty($assocArr)) {
            return false;
        }

        $sign = $assocArr['rsaSign'];
        unset($assocArr['rsaSign']);

        // 参数按字典顺序排序
        ksort($assocArr); 

        $parts = [];
        foreach ($assocArr as $k => $v) {
            $parts[] = $k . '=' . $v;
        }
        $str = implode('&', $parts);

        $res = (new Rsa($this->rsa_public_key, null))->verify($str, $sign);

        return $res;
    }

    /**
     * 渠道回调数据返回
     */
    public function returnMsg($code = 0, $msg = 'success', $isConsumed = 2)
    {
        $result = [
            'errno' => $code,
            'msg' => $msg,
        ];
        if($code == 0){
            $result['data'] = [
                'isConsumed' => $isConsumed
            ];
        }
        return json($result);
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