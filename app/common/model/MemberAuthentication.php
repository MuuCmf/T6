<?php
namespace app\common\model;

class MemberAuthentication extends Base
{
    protected $autoWriteTimestamp = true;
    protected $_card_type = [
        0 => '身份证',
    ];
    protected $_status  = [
        -1 => '审核未通过',
        0  => '未认证',
        1  => '待审核',
        2  => '已实名认证',
    ];

    public function handle($data)
    {
        $data['user_info'] = query_user($data['uid']);

        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }

        if(isset($data['card_type'])){
            $data['card_type_str'] = $this->_card_type[$data['card_type']];
        }

        // 图片处理
        if(isset($data['front'])){
            $data['front_original'] = get_attachment_src($data['front']);
        }
        if(isset($data['back'])){
            $data['back_original'] = get_attachment_src($data['back']);
        }

        //时间戳格式化
        $data['create_time_str'] = time_format($data['create_time']);
        $data['update_time_str'] = time_format($data['update_time']);
        
        return $data;
    }
}