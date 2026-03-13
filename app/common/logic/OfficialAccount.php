<?php

namespace app\common\logic;

/**
 * 微信公众号逻辑类
 * Class OfficialAccount
 * @package app\common\service\wechat
 */
class OfficialAccount extends Base
{
    public function formatData($data)
    {
        $data = $this->setImgAttr($data, '1:1');
        $data = $this->setImgAttr($data, '1:1', 'qrcode');

        return $data;
    }
}
