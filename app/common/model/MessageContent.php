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
     * 写入消息内容
     */
    public function addMessageContent($shopid, $title, $description, $content, $args = '')
    {
        // 写入消息内容
        $content_data = [
            'shopid' => $shopid,
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'args' => $args,
            'status' => 1
        ];
        // 返回主键ID
        $this->save($content_data);
        if(!empty($this->id)){
            // 返回主键ID
            return $this->id;
        }
        
        return false;
    }

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