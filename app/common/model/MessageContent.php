<?php
namespace app\common\model;

class MessageContent extends Base
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
        
        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }
        //时间戳格式化
        $data['create_time_str'] = time_format($data['create_time']);
        $data['update_time_str'] = time_format($data['update_time']);
        
        return $data;
    }

}