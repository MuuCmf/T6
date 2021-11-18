<?php
namespace app\articles\model;

use app\articles\model\ArticlesBase as Base;

/**
 * 应用配置
 */
class ArticlesConfig extends Base
{
    /**
     * 初始化数据
     * @return [type] [description]
     */
    public function defaultData()
    {
        $data = [
            'id' => 0,
            'shop_id' => 0,
            'config' => [
                'comment' => [
                    'switch' => 0,
                ]
            ],
            'status' => 1,
        ];
        return $data;
    }
}