<?php
namespace app\common\model;

/******************店铺自定义页面模型******************/
class Page extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * 店铺风格
     *
     * @var        array
     */
    public $_style = [

    ];

    /**
     * Gets the count.
     *
     * @param      <type>  $map    The map
     *
     * @return     <type>  The count.
     */
    public function getCount($map)
    {
        return $this->where($map)->count();
    }

}