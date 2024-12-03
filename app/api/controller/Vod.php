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

        echo $signature;
        echo "\n";
    }
}
