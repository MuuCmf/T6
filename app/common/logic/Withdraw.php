<?php
namespace app\common\logic;

use app\channel\logic\Channel;

/**
 * @title 提现逻辑类
 * Class Withdraw
 * @package app\common\logic
 */
class Withdraw extends Base
{
    public $_paid = [
        0 => '提现中',
        1 => '已完成'
    ];

    public $_error = [
        0 => '成功',
        1 => '失败'
    ];

    public function formatData($data)
    {
        //用户数据
        $data['price'] = sprintf("%.2f",$data['price']/100);
        $data['paid_str'] = $this->_paid[$data['paid']];
        $data['error_str'] = $this->_error[$data['error']];
        if($data['paid_time'] == 0){
            $data['paid_time_str'] = '未完成';
        }else{
            $data['paid_time_str'] = time_format($data['paid_time']);
        }
        $data['openid'] = get_openid($data['shopid'], $data['uid'],$data['channel']);
        $data['user_info'] = query_user($data['uid'],['nickname','avatar']);
        $data['channel_str'] = Channel::$_channel[$data['channel']];

        if(!empty($data['error_msg'])){
            $data['error_msg'] = json_decode($data['error_msg'], true);
        }
        $data = $this->setTimeAttr($data);
        return $data;
    }
}