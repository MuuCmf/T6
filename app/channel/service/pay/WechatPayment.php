<?php
namespace app\channel\service\pay;

use EasyWeChat\Factory;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;
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
        $serial = config('extend.WX_PAY_CERT_SERIAL');
        $platform_serial = config('extend.WX_PAY_WITHDRAW_PLATFORM_SERIAL');
        if (empty($mchid)){
            throw new Exception('请填写商户ID');
        }
        if (empty($key)){
            throw new Exception('请填写商户密钥');
        }
        if (empty($serial)){
            throw new Exception('请填写商户API证书序列号');
        }
        return [
            'app_id' => $appid,
            'mch_id' => $mchid,
            'key' => $key,
            'serial' => $serial,  // 商户API证书序列号
            'cert_path' => app()->getRootPath() . 'public/attachment/' . config('extend.WX_PAY_CERT'),
            'key_path' => app()->getRootPath() . 'public/attachment/' . config('extend.WX_PAY_KEY'),
            'platform_serial' => $platform_serial,  // 微信支付平台证书序列号
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
        if ($res['result_code'] == 'FAIL'){
            throw new Exception($res['err_code'] . ':' . $res['err_code_des']);
        }
        if($trade_type == 'JSAPI'){
            $res = $this->app->jssdk->sdkConfig($res['prepay_id']);
        }
        
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

    /**
     * 商家转账到零钱
     */
    public function toBalanceV3($data)
    {
        try {
            // 商户号
            $merchantId = $this->config['mch_id'];
            // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
            $merchantPrivateKeyFilePath = 'file://' . $this->config['key_path'];
            $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
            // 「商户API证书」的「证书序列号」
            $merchantCertificateSerial = $this->config['serial'];
            // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
            $platformCertificateFilePath = 'file://' . app()->getRootPath() . 'public/attachment/cert/wechatpay_' .$this->config['platform_serial']. '.pem';
            $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
            // 从「微信支付平台证书」中获取「证书序列号」
            $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
            // 构造一个 APIv3 客户端实例
            $instance = Builder::factory([
                'mchid'      => $merchantId,
                'serial'     => $merchantCertificateSerial,
                'privateKey' => $merchantPrivateKeyInstance,
                'certs'      => [
                    $platformCertificateSerial => $platformPublicKeyInstance,
                ],
            ]);

            $resp = $instance
            ->chain('v3/transfer/batches')
            ->post(['json' => $data]);
            
            // echo $resp->getStatusCode(), PHP_EOL;
            // echo $resp->getBody(), PHP_EOL;
            return [
                'return_code' => 'SUCCESS',
                'result_code' => 'SUCCESS',
                'status_code' => $resp->getStatusCode(),
                'body' => json_decode($resp->getBody(), true)
            ];

        } catch (\Exception $e) {
            // 进行错误处理
            //echo $e->getMessage(), PHP_EOL;
            return [
                'errCode' => 0,
                'errMsg' => $e->getMessage()
            ];
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                $r = $e->getResponse();
                echo $r->getStatusCode() . ' ' . $r->getReasonPhrase(), PHP_EOL;
                echo $r->getBody(), PHP_EOL;
                exit;
            }
            //echo $e->getTraceAsString(), PHP_EOL;
        }
    }

    /**
     * 获取APIv3微信支付平台证书
     * 首次手动下载命令（在vendor/wechatpay/wechatpay目录下执行）
     * composer exec CertificateDownloader.php -- -m 商户号 -s 商户证书序列号 -f 商户的私钥文件 -k ApiV3Key -o 保存的路径
     * 完整示范
     * composer exec CertificateDownloader.php -- -m 1602403282 -s 1C5A97B726EB7EA5EC1212E0CEC14C758C1B427A -f /www/wwwroot/demo.t6.muucmf.cc/public/attachment/file/20230610/30b866b787758af61351edef055206e8.pem -k E0DBCB26C939DEA508A33988CEAFAE79 -o /www/wwwroot/demo.t6.muucmf.cc/public/attachment/cert
     */
    public function getFormCert()
    {
        // 商户号
        $merchantId = $this->config['mch_id'];
        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyFilePath = 'file://' . $this->config['key_path'];
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);

        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = $this->config['serial'];

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificateFilePath = 'file://' . app()->getRootPath() . 'public/attachment/cert/wechatpay_'.$this->config['platform_serial'] . '.pem';
        $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);

        // 构造一个 APIv3 客户端实例
        $instance = Builder::factory([
            'mchid'      => $merchantId,
            'serial'     => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs'      => [
                $platformCertificateSerial => $platformPublicKeyInstance,
            ],
        ]);

        // 发送请求
        $resp = $instance->chain('v3/certificates')->get(
            ['debug' => false] // 调试模式，https://docs.guzzlephp.org/en/stable/request-options.html#debug
        );

        //echo $resp->getBody(), PHP_EOL;
        $res = json_decode($resp->getBody(), true);

        if(is_array($res) && !empty($res['data'])){
            foreach($res['data'] as $v){
                $cert_content = $this->decryptToString($v['encrypt_certificate']['associated_data'], $v['encrypt_certificate']['nonce'], $v['encrypt_certificate']['ciphertext']);

                $path = app()->getRootPath() . 'public/attachment/cert/wechatpay_' . $v['serial_no'] . '.pem';
                @file_put_contents($path, $cert_content);
                chmod($path, 0777);
            }
        }
    }

    const KEY_LENGTH_BYTE = 32;
	const AUTH_TAG_LENGTH_BYTE = 16;
    /**
	 * Decrypt AEAD_AES_256_GCM ciphertext
	 *
	 * @param string    $associatedData     AES GCM additional authentication data
	 * @param string    $nonceStr           AES GCM nonce
	 * @param string    $ciphertext         AES GCM cipher text
	 *
	 * @return string|bool      Decrypted string on success or FALSE on failure
	 */
	public function decryptToString($associatedData, $nonceStr, $ciphertext) {
		$ciphertext = \base64_decode($ciphertext);
		if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
			return false;
		}

		// ext-sodium (default installed on >= PHP 7.2)
		if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
			return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->config['key']);
		}

		// openssl (PHP >= 7.1 support AEAD)
		if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
			$ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
			$authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

			return \openssl_decrypt($ctext, 'aes-256-gcm', $this->config['key'], \OPENSSL_RAW_DATA, $nonceStr,
				$authTag, $associatedData);
		}

		throw new \RuntimeException('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
	}
}