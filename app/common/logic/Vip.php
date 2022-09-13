<?php
namespace app\common\logic;

use app\common\model\VipCard as VipCardModel;
use app\common\logic\VipCard as VipCardLogic;

class Vip extends Base
{

    public $_status = [
        1 => '启用',
        0 => '已禁用',
        -1 => '已删除',
        -2 => '已过期'
    ];

    /**
     * @title 数据格式化
     * @param $data
     */
    public function formatData($data){
        $VipCardModel = new VipCardModel();
        if(!empty($data)){
            $data['user_info'] = query_user($data['uid']);
            //获取所持有会员卡数据
            $card_data = $VipCardModel->find($data['card_id']);
            if(is_object($card_data)){
                $card_data = $card_data->toArray();
            }
            $data['vip_card_info'] = (new VipCardLogic())->formatData($card_data);

            $data = $this->setStatusAttr($data,$this->_status);
            $data = $this->setTimeAttr($data);
            if($data['end_time'] == 0){
                $data['end_time_str'] = '永久';
            }
        }
        return $data;
    }
}