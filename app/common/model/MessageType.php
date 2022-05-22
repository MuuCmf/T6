<?php
namespace app\common\model;

use app\common\model\Message as MessageModel;

class MessageType extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    public $_status  = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '删除',
    ];
    
    /**
     * 数据处理
     */
    public function formatData($data)
    {
        if(empty($data['icon'])){
            $data['icon_80'] = request()->domain() . '/static/common/images/nopic.png';
        }else{
            $data['icon_80'] = get_thumb_image($data['icon'], 80, 80);
        }

        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }
        

        return $data;
    }

}