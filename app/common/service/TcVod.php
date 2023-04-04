<?php
namespace app\common\service;

use think\Exception;

/**
 * 腾讯云点播签名计算
 */
class TcVod
{
    /**
     * 获取签名
     */
    public function getSignature($secretId='', $secretKey='', $subAppId=''){
        // 确定签名的当前时间和失效时间
        $current = time();
        $expired = intval($current + 86400);  // 签名有效期：1天

        // 向参数列表填入参数
        $arg_list = [
            "secretId" => $secretId,
            "currentTimeStamp" => intval($current),
            "expireTime" => intval($expired),
            "random" => rand(),
            "vodSubAppId" => intval($subAppId),
            "taskNotifyMode" => "none"
        ];
        // 判断是否开启上传后转码加密任务流
        $procedure = config('extend.VOD_TENCENT_PROCEDURE');
        if($procedure == 1){
            $arg_list['procedure'] = "SimpleAesEncryptPreset"; // 系统预置任务流
        }

        // 计算签名
        $original = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $original, $secretKey, true).$original);

        return $signature;
    }

    /**
     * 获取腾讯云点播KEY防盗链完整url
     * @ $exper 试看时间 单位（秒）
     */
    public function getKeyMediaUrl($media_url, $exper = 0)
    {
        $key = config('extend.VOD_TENCENT_KEY_VALUE');

        try {
            $dir_arr = explode('/', $media_url);

            $dir = '/' . $dir_arr[3] . '/' . $dir_arr[4] . '/';
            $t = time() + 7200;
            $t = dechex($t);
            if($exper == 0){
                $sign = md5($key . $dir . $t);
                $return_media_url = $media_url . '?t=' . $t . '&sign=' . $sign;
            }else{
                $sign = md5($key . $dir . $t . $exper);
                $return_media_url = $media_url . '?t=' . $t . '&exper=' . $exper . '&sign=' . $sign;
            }
            
            return $return_media_url;
        } catch (Exception $e) {
            return $media_url;
        }
    }

    /**
     * Psign
     * 腾讯云播放器签名
     * 试看时长，单位为秒，以十进制表示。
     * 如果要指定试看时长，时长必须不小于30秒。
     * 具体含义和取值参见 防盗链参数 中的 exper 参数。

     */
    public function getPsign($fileId, $exper = 0)
    {
        $subAppId = config('extend.VOD_TENCENT_SUBAPPID');
        $key = config('extend.VOD_TENCENT_PLAYER_KEY');

        $currentTime = time();
        $psignExpire = $currentTime + 7200; // 可任意设置过期时间，示例1h
        $urlTimeExpire = dechex($psignExpire); // 可任意设置过期时间，16进制字符串形式，示例1h

        // 判断是否开启上传后转码加密任务流
        $procedure = config('extend.VOD_TENCENT_PROCEDURE');
        if($procedure == 1){
            // 私有加密或 DRM 保护的 转自适应码流 输出。
            $audioVideoType = 'ProtectedAdaptive';
        }else{
            // 上传 的原始音视频。
            $audioVideoType = 'Original';
        }
        $contentInfo = [
            "audioVideoType" => $audioVideoType,
        ];
        // 开启防盗链key时
        if($procedure == 1){
            $contentInfo["drmAdaptiveInfo"]["privateEncryptionDefinition"] = 12;
        }

        $urlAccessInfo = [
            "exper" => $exper,
            "t" => $urlTimeExpire
        ];
        if($exper > 0){
            $urlAccessInfo['exper'] = $exper;
        }

        $payload = array(
            "appId" => intval($subAppId),
            "fileId" => $fileId,
            "contentInfo" => $contentInfo,
            "currentTimeStamp" => $currentTime,
            "expireTimeStamp" => $psignExpire,
            "urlAccessInfo" => $urlAccessInfo
        );

        $jwt = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');

        return $jwt;
    }

}