<?php
namespace app\channel\logic;

/**
 * @title 模板消息处理类
 * Class TemplateMessage
 * @package app\channel\logic
 */
class TemplateMessage{
    //授权类型
    public $oauth_type = [
        'weixin_h5'     => [
            'title' => '微信公众号',
            'template' => [
                ['title' => '订单支付成功通知','tips' => '选择行业为：“IT科技 - 互联网|电子商务”，添加标题为：”订单支付成功通知“，编号为：“OPENTM2074989020”的模板后将模板ID输入文本框中。' ,'input_name' => 'pay_success'],
                ['title' => '审核结果通知','tips' => '选择行业为：“IT科技 - 互联网|电子商务”，添加标题为：”审核结果通知“，编号为：“OPENTM411984401”的模板后将模板ID输入文本框中。' ,'input_name' => 'audit'],
            ]
        ],
        'weixin_app'    => [
            'title' => '微信小程序',
            'template' => [
                ['title' => '订单支付成功通知','tips' => '模板格式为 用户名、订单号、订单金额、商品信息、备注','input_name' => 'pay_success'],
                ['title' => '审核结果通知','tips' => '模板格式为 主题、时间、审核结果、审核意见','input_name' => 'audit'],
            ]
        ],
        'alipay_app'    => '支付宝小程序'
    ];

    /**
     * @title 格式化数据
     */
    public function formatData($data){
        if (!empty($data)){
            $data = json_decode($data,true);
            $data['manager_info'] = query_user($data['manager_uid']);
        }else{
            $data = [
                'switch'        => 0,
                'to'            => [],
                'manager_uid'   => 0,
                'manager_info'  => [],
                'tmplmsg'       => []
            ];
        }
        return $data;
    }
}