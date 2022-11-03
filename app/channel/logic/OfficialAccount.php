<?php
namespace app\channel\logic;

use app\common\logic\Base as MuuBase;


/**
 * 微信公众号逻辑类
 * Class OfficialAccount
 * @package app\common\service\wechat
 */
class OfficialAccount extends MuuBase
{
    public function formatData($data){
        $data = $this->setImgAttr($data,'1:1');
        $data = $this->setImgAttr($data,'1:1','qrcode');

        return $data;
    }
}