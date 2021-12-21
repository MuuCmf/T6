<?php
namespace app\common\model;

class Announce extends Base
{   
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function getList($map, $limit=10, $order = 'create_time desc' ,$field = '*')
    {
        $list = $this->where($map)->limit($limit)->order($order)->field($field)->select();
        
        return $list;
    }
    
}