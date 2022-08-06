<?php
namespace app\api\controller;

use app\common\controller\Api;

class Qrcode extends Api
{
    /**
     * 生成二维码 输出图片
     */
    public function create($url){
        $url = str_replace('./','',urldecode($url));
        $qrcode = qrcode($url,false,false,false,'8','L',2,false);
        echo $qrcode;exit();
    }
}