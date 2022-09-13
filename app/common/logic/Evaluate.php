<?php
namespace app\common\logic;
/**
 * 评价逻辑类
 * Class Evaluate
 * @package app\common\logic
 */
class Evaluate extends Base{
    public function formatData($data)
    {
        if(!empty($data)){
            // 获取用户
            $data['user_info'] = query_user($data['uid']);
            // 处理评价图片
            if($data['images'] !== null || $data['images'] !== 'null' || !empty($data['images'])){
                $data['images'] = json_decode($data['images'],true);
                if(is_array($data['images']) && !empty($data['images'])){
                    $temp_images = [];
                    foreach($data['images'] as $key=>$val){
                        $thumb = get_thumb_image($val, 60 , 60);
                        $temp_images[$key]['thumb'] = $thumb;
                        $temp_images[$key]['preview'] = get_attachment_src($val);
                        $temp_images[$key]['image'] = $val;
                    }
                    $data['images'] = $temp_images;
                }
            }
            $data = $this->setTimeAttr($data);
            //是否允许修改，30天内的评价可修改一次
            $time_30 = $data['create_time'] + 30*86400;
            if($time_30>$data['create_time']){
                $data['can_edit'] = 1;
            }else{
                $data['can_edit'] = 0;
            }
            if($data['update_time'] > $data['create_time']){
                $data['can_edit'] = 0;
            }
        }
        return $data;
    }
}