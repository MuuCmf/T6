<?php
namespace app\channel\logic;

class Tominiprogram {

    protected $_type = [
        'weixin_app' => '微信小程序',
        'alipay_app' => '支付宝小程序',
        'baidu_app'  => '百度小程序'
    ];
    /**
     * @title 格式化数据
     */
    public function formatData($data){
        $data['type_str'] = $this->_type[$data['type']];
        $data['qrcode'] = get_attachment_src($data['qrcode']);
        $data = $this->setTimeAttr($data);
        return $data;
    }


    private function setTimeAttr($data)
    {
        if(!empty($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
            $data['create_time_friendly_str'] = friendly_date($data['create_time']);
        }
        if(!empty($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
            $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        }
        if(!empty($data['start_time'])){
            $data['start_time_str'] = time_format($data['start_time']);
        }
        if(!empty($data['end_time'])){
            $data['end_time_str'] = time_format($data['end_time']);
        }
        if(!empty($data['use_time'])){
            $data['use_time_str'] = time_format($data['use_time']);
        }
        if(!empty($data['paid_time'])){
            $data['paid_time_str'] = time_format($data['paid_time']);
        }
        if(!empty($data['logistic_time'])){
            $data['logistic_time_str'] = time_format($data['logistic_time']);
        }
        if(!empty($data['reply_time'])){
            $data['reply_time_str'] = time_format($data['reply_time']);
        }


        return $data;
    }
}