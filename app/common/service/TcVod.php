<?php
namespace app\common\service;

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
        $arg_list = array(
            "secretId" => $secretId,
            "currentTimeStamp" => intval($current),
            "expireTime" => intval($expired),
            "random" => rand(),
            "vodSubAppId" => intval($subAppId)
        );

        // 计算签名
        $original = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $original, $secretKey, true).$original);

        return $signature;
    }

}