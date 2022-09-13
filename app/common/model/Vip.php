<?php
namespace app\common\model;

/**
 * 付费会员模型
 */
class Vip extends Base
{
    protected $autoWriteTimestamp = true;

    public function getVipByCardId($shopid, $uid, $card_id)
    {
        $raw = "`uid` = :uid AND `shopid` = :shopid AND `card_id` = :card_id AND `status`=1 AND (`end_time` > :now_time OR `end_time` = :zero)";
        $raw_arr = [
            'uid' => $uid,
            'shopid' => $shopid,
            'card_id' => $card_id,
            'now_time' => time(),
            'zero' => 0
        ];

        $res = $this->whereRaw($raw, $raw_arr)->find();
        if($res){
            return $res;
        }

        return false;
    }

}