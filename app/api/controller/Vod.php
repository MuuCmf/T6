<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\service\TcVod;

class Vod extends Api
{
    public function sign()
    {
        $TcVod = new TcVod();
        $signature = $TcVod->getSignature();
        $subAppId = config('extend.VOD_TENCENT_SUBAPPID');
        //echo $signature;
        $result = [
            "signature" => $signature,
            "subAppId" => $subAppId,
        ];
        return $this->success('success', $result);
    }
}
