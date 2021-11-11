<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Qrcode.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/11
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\controller\Base;

class Qrcode extends Base{
    /**
     * 生成二维码 输出图片
     */
    public function qrcode($url){
        $url = str_replace('./','',urldecode($url));
        $qrcode = qrcode($url,false,false,false,'8','L',2,false);
        echo $qrcode;exit();
    }
}