<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\ScoreLog;
use app\common\model\Member as MemberModel;

/**
 * 积分相关接口
 */
class Score extends Api
{
    /**
     * 积分日志
     */
    public function log()
    {
        $uid = get_uid();
        $map = [
            ['uid' ,'=' ,$uid],
        ];

        $rows = input('rows', 10, 'intval');
        $order_field = input('order_field', 'create_time', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = (new ScoreLog())->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val['create_time_str'] = time_format($val['create_time']);
            $val['create_time_friendly_str'] = friendly_date($val['create_time']);
        }
        unset($val);

        return $this->success('success',$lists);
    }
}