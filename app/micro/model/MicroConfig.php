<?php
namespace app\common\model;

use app\common\model\Base;

/******************店铺配置模型******************/
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
            'title' => 'MuuCmf',
            'cover' => request()->domain() .'/static/common/images/default_logo.png',
            'style' => 'Blue',
            'thumb' => '4:3',
            'mobile_bind' => 0,
            'show_view' => 0,
            'show_sale' => 0,
            'show_favorites' => 0,
            'show_marking_price' => 0,
            'status' => 1,
        ];
        return $data;
    }
}