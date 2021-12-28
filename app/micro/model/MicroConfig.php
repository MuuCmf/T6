<?php
namespace app\micro\model;

use app\common\model\Base;

/******************配置模型******************/
class MicroConfig extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * 初始化数据
     * @return [type] [description]
     */
    public function defaultData()
    {
        $data = [
            'style' => 'Blue',
            'status' => 1,
        ];
        return $data;
    }
}