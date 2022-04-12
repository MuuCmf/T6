<?php
namespace app\common\logic;

/*
 * MuuCmf
 * 用户反馈逻辑层
 */

use app\common\model\Module;

class Feedback
{
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
        if (!is_array($data['user_info'])) {
            $data['user_info'] = [];
            $data['user_info']['nickname'] = '用户已注销';
            $data['user_info']['avatar'] = request()->domain() . '/static/common/images/default_avatar.jpg';
        }
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