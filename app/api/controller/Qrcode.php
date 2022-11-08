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
        ob_clean();//这个一定要加上，清除缓冲区
        $qrcode = qrcode($url,false,false,false,'8','L',2,false);
        echo $qrcode;exit();
    }
}