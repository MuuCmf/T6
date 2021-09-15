<?php
namespace app\common\service;

use think\facade\Config;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

// Download：https://github.com/aliyun/openapi-sdk-php
// Usage：https://github.com/aliyun/openapi-sdk-php/blob/master/README.md
class Aliyun
{

    /**
     * 发送短信
     */
    public function sendSms($PhoneNumbers, $code)
    {
        $access_key_id = config('extend.SMS_ALIYUN_ACCESSKEYID');
        dump($access_key_id);exit;
        $access_key_secret = config('extend.SMS_ALIYUN_ACCESSKEYSECRET');
        AlibabaCloud::accessKeyClient($access_key_id, $access_key_secret)->regionId('cn-beijing')->asDefaultClient();
        

        $params = [
            'code' => $code
        ];
        $params = json_encode($params);
        // 短信签名
        $smsSign = config('extend.SMS_ALIYUN_SIGN');
        // 短信模板
        $smsTemplateId = config('extend.SMS_ALIYUN_TEMPLATEID');
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                            'query' => [
                                'PhoneNumbers' => $PhoneNumbers,
                                'SignName' => $smsSign,
                                'TemplateParam' => $params,
                                'TemplateCode' => $smsTemplateId,
                            ],
                        ])
                ->request();
            // print_r($result->toArray());
            $result = $result->toArray();
            return $result;
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}
