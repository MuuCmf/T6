<?php
namespace app\channel\model;

use app\common\model\Base;

/**
 * 抖音结算分账模型
 */
class DouyinMpSettle extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    public $_status = [
        0 => '结算中',
        1 => '已完成'
    ];

    /**
     * 处理数据
     */
    public function handle($data)
    {
        $data['price'] = sprintf("%.2f",floatval($data['price']/100));
        $data['status_str'] = $this->_status[$data['status']];

        $data['create_time_str'] = time_format($data['create_time']);
        $data['create_time_friendly_str'] = friendly_date($data['create_time']);
    
        $data['update_time_str'] = time_format($data['update_time']);
        $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        
        

        return $data;
    }

    
}