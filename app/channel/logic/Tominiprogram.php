<?php

namespace app\channel\logic;

use app\common\logic\Base;

class Tominiprogram extends Base
{

    protected $_type = [
        'weixin_app' => '微信小程序',
        'alipay_app' => '支付宝小程序',
        'baidu_app'  => '百度小程序'
    ];
    /**
     * @title 格式化数据
     */
    public function formatData($data)
    {
        $data['type_str'] = $this->_type[$data['type']];

        $data = $this->setImgAttr($data, '1:1', 'qrcode');
        $data = $this->setTimeAttr($data);
        return $data;
    }

}
