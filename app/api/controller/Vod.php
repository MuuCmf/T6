<?php
namespace app\api\controller;

use app\common\controller\Base;
use app\common\model\Attachment;
use app\common\service\TcVod;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */

class Vod extends Base
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