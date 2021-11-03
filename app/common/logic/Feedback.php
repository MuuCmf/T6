<?php
namespace app\common\logic;

/*
 * MuuCmf
 * 用户反馈逻辑层
 */
class Feedback 
{
    public $_status  = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '删除',
    ];

    /**
     * 格式化数据
     */
    public function _formatData($data)
    {   

        
        return $data;
    }


}