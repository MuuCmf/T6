<?php
namespace app\common\model;

class MessageContent extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * 数据处理
     */
    public function formatData($data)
    {
        
        

        return $data;
    }

}