<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\TcVod;

class Vod extends Api
{
    public function sign()
    {
        $secretId = config('extend.VOD_TENCENT_SECRETID');
        $secretKey = config('extend.VOD_TENCENT_SECRETKEY');
        $subAppId = config('extend.VOD_TENCENT_SUBAPPID');
        $TcVod = new TcVod();
        $signature = $TcVod->getSignature($secretId, $secretKey, $subAppId);

        echo $signature;
        echo "\n";
    }

}