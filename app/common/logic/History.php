<?php

namespace app\common\logic;

use app\common\model\Module;

class History extends Base
{
    public function formatData($data)
    {
        // 获取应用信息
        $data['app_info'] = (new Module())->getModule($data['app']);

        // 解析metadata
        $data['metadata'] = json_decode($data['metadata'], true);

        $data['products'] = $data['metadata'];
        if(isset($data['products']['cover'])){
            $data['products']['cover'] = $data['products']['cover'];
            $data['products']['cover_100'] = get_thumb_image($data['metadata']['cover'], 100, 100);
            $data['products']['cover_200'] = get_thumb_image($data['metadata']['cover'], 200, 200);
            $data['products']['cover_300'] = get_thumb_image($data['metadata']['cover'], 300, 300);
            $data['products']['cover_400'] = get_thumb_image($data['metadata']['cover'], 400, 400);
            $data['products']['cover_800'] = get_thumb_image($data['metadata']['cover'], 800, 800);
        }

        if (isset($data['metadata']['price'])) {
            $data['products']['price'] = sprintf("%.2f", $data['metadata']['price'] / 100);
        }
        
        $data['info_id'] = (string)$data['info_id'];
        
        // 获取用户信息
        $data['user_info'] = query_user($data['uid'], ['nickname', 'username', 'avatar']); 

        $data = $this->setTimeAttr($data);

        return $data;
    }
}
