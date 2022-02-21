<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Withdraw.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/2/20
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\logic;
use app\channel\logic\Channel;

/**
 * @title 提现逻辑类
 * Class Withdraw
 * @package app\common\logic
 */
class Withdraw extends Base{
    public $_paid = [
        0 => '提现中',
        1 => '已完成'
    ];

    public $_error = [
        0 => '成功',
        1 => '失败'
    ];

    public function _formatData($data){
        //用户数据
        $data['price'] = sprintf("%.2f",$data['price']/100);
        $data['paid_str'] = $this->_paid[$data['paid']];
        $data['error_str'] = $this->_error[$data['error']];
        if($data['paid_time'] == 0){
            $data['paid_time_str'] = '未提现';
        }else{
            $data['paid_time_str'] = time_format($data['paid_time']);
        }
        $data['openid'] = get_openid($data['uid'],$data['channel']);
        $data['user_info'] = query_user($data['uid'],['nickname','avatar']);
        $data['channel_str'] = Channel::$_channel[$data['channel']];
        $data = $this->setTimeAttr($data);
        return $data;
    }

    /**
     * @title 获取提现配置
     * @return array
     */
    public function getConfig(){
        $config = [
            'status' => config('extend.WITHDRAW_STATUS'),
            'tax_rate' => config('extend.WITHDRAW_TAX_RATE'),
            'day_num' => config('extend.WITHDRAW_DAY_NUM'),
            'min_price' => config('extend.WITHDRAW_MIN_PRICE'),
            'max_price' => config('extend.WITHDRAW_MAX_PRICE'),
        ];
        return $config;
    }
}