<?php
namespace app\common\model;

use think\Model;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use TencentCloud\Sms\V20210111\SmsClient;
// 导入要请求接口对应的Request类
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Credential;
// 导入可选配置类
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use function GuzzleHttp\json_decode;

class Verify extends Model
{
    protected $tableName = 'verify';
    protected $autoWriteTimestamp = true;
    // 关闭自动写入update_time字段
    protected $updateTime = false;

    /**
     * 写入验证码随机数
     */
    public function addVerify($account, $type, $uid = 0)
    {
        $uid = $uid ? $uid:is_login();
        if ($type == 'mobile' || $type == 'email') {
            $verify = create_rand(6, 'num');
        } else {
            $verify = create_rand(32);
        }

        $this->where(['account'=>$account,'type'=>$type])->delete();
        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        
        $res = $this->save($data);
        if(!$res){
            return false;
        }
        return $verify;
    }

    
    /**
     * 检测验证码
     *
     * @param      <type>   $account  The account
     * @param      <type>   $type     The type
     * @param      <type>   $verify   The verify
     * @param      <type>   $uid      The uid
     *
     * @return     boolean  ( description_of_the_return_value )
     */
    public function checkVerify($account, $type = 'mobile', $verify){

        $data = $this->where(['account'=>$account,'type'=>$type,'verify'=>$verify])->find();

        if(!$data){
            return false;
        }
        $this->where(['account'=>$account, 'type'=>$type])->delete();
        $this->where('create_time', '<=', get_some_day(1))->delete();

        return true;
    }

    /**
     * 发送短信
     */
    public function sendSms($PhoneNumbers, $code)
    {
        $smsDriver = config('extend.SMS_SEND_DRIVER');

        // 通过阿里云发送短信
        if($smsDriver == 'aliyun'){
            $result = $this->aliyun($PhoneNumbers, $code);
            return $result;
        }

        // 通过腾讯云发送短信
        if($smsDriver == 'tencent'){
            $result = $this->tencent($PhoneNumbers, $code);
            return $result;
        }

        return false;
    }

    /**
     * 通过阿里云发送短信
     */
    public function aliyun($PhoneNumbers, $code)
    {
        $access_key_id = config('extend.SMS_ALIYUN_ACCESSKEYID');
        $access_key_secret = config('extend.SMS_ALIYUN_ACCESSKEYSECRET');
        $region = config('extend.SMS_ALIYUN_REGION');
        AlibabaCloud::accessKeyClient($access_key_id, $access_key_secret)->regionId($region)->asDefaultClient();
        
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
            // echo $e->getErrorMessage() . PHP_EOL;
            return $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            // echo $e->getErrorMessage() . PHP_EOL;
            return $e->getErrorMessage() . PHP_EOL;
        }
    }

    /**
     * 通过腾讯云发送短信
     */
    public function tencent($PhoneNumbers, $code)
    {
        $SecretId = config('extend.SMS_TENCENT_SECRETID');
        $SecretKey = config('extend.SMS_TENCENT_SECRETKEY');
        $SdkAppId = config('extend.SMS_TENCENT_APPID');
        $Sign = config('extend.SMS_TENCENT_SIGN');
        // 短信模板
        $TemplateId = config('extend.SMS_TENCENT_TEMPLATEID');
        // 区域参数
        $region = config('extend.SMS_TENCENT_REGION');
        try {
            $cred = new Credential($SecretId, $SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("sms.tencentcloudapi.com");
            
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new SmsClient($cred, $region, $clientProfile);

            $req = new SendSmsRequest();
            
            $params = array(
                "PhoneNumberSet" => array( $PhoneNumbers ),
                "SmsSdkAppId" => $SdkAppId,
                "SignName" => $Sign,
                "TemplateId" => $TemplateId,
                "TemplateParamSet" => array( $code )
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->SendSms($req);

            //print_r($resp->toJsonString());
            //dump($resp);exit;
            $resp = $resp->toJsonString();
            $resp = json_decode($resp, true);
            return $resp;
        }
        catch(TencentCloudSDKException $e) {
            // echo $e;
            return $e->getErrorCode();
        }
    }



}