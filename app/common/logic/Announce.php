<?php
namespace app\common\logic;

class Announce
{   
    protected $_type = [
        0=> '文字',
        1=> '图片'
    ];

    public $_status  = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '删除',
    ];

    /**
     * 格式化数据
     */
    public function formatData($data)
    {   
        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }

        if(isset($data['type'])){
            $data['type_str'] = $this->_type[$data['type']];
        }
        
        // 图片处理
        if(isset($data['cover'])){
            $data['cover_src_80'] = get_thumb_image($data['cover'], 80, 80);
            $data['cover_src_120'] = get_thumb_image($data['cover'], 120, 120);
            $data['cover_src_200'] = get_thumb_image($data['cover'], 200, 200);
            $data['cover_src_400'] = get_thumb_image($data['cover'], 400, 400);
        }

        // 连接至数据处理
        if(!empty($data['link_to'])){
            $data['link'] = json_decode($data['link_to'], true);
        }

        //时间戳格式化
        $data['create_time_str'] = time_format($data['create_time']);
        $data['update_time_str'] = time_format($data['update_time']);
        
        return $data;
    }
}