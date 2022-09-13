<?php
namespace app\common\model;

/**
 * 用户反馈模型
 */
class Feedback extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true;
    
    public $_status  = [
        '1'  => '已处理',
        '0'  => '未处理',
        '-1' => '删除',
    ];

    /**
     * 格式化数据
     */
    public function formatData($data)
    {   

        $data['user_info'] = query_user($data['uid']);
        $module = (new Module())->where('name',$data['app'])->find();
        $data['app_alias'] = $module['alias'] ?? '';

        $data['images_format'] = [];
        if (!empty($data['images'])){
            $images = explode(',',$data['images']);
            foreach($images as $k=>$v){
                $data['images_format'][$k]['original'] = $v;
                $data['images_format'][$k]['format'] = get_attachment_src($v);
            }
        }
        $data['status_str'] = $this->_status[$data['status']];
        //时间戳格式化
        $data['create_time_str'] = time_format($data['create_time']);
        $data['update_time_str'] = time_format($data['update_time']);
        return $data;
    }
}