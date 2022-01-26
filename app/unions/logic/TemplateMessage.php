<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: TemplateMessage.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/1/26
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\logic;
/**
 * @title 模板消息处理类
 * Class TemplateMessage
 * @package app\unions\logic
 */
class TemplateMessage{
    //授权类型
    public $oauth_type = [
        'weixin_h5'     => [
            'title' => '微信公众号',
            'template' => [
                ['title' => '订单支付成功通知','id' => 'OPENTM2074989020','input_name' => 'pay_success'],
                ['title' => '审核结果通知','id' => 'OPENTM411984401','input_name' => 'audit'],
            ]
        ],
        'weixin_app'    => [
            'title' => '微信小程序',
            'template' => [
                ['title' => '订单支付成功通知','id' => 'OPENTM2074989020','input_name' => 'pay_success'],
                ['title' => '审核结果通知','id' => 'OPENTM411984401','input_name' => 'audit'],
            ]
        ],
        'alipay_app'    => '支付宝小程序'
    ];

    /**
     * @title 格式化数据
     */
    public function _formatData($data){
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