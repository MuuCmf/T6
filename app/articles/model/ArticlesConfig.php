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
                    'switch_str' => '禁用',
                ]
            ],
            'status' => 1,
            'status_str' => '启用'
        ];
        return $data;
    }
}