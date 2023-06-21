<?php
 
namespace app\common\listener;

use app\common\model\Search;

class SearchIndex
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($data)
    {
        if(!empty($data['app']) && $data['app'] != 'admin' && !empty($data['title'] && !empty($data['description']) && isset($data['cover']))){
            (new Search())->add($data['shopid'], $data['app'], $data['info_id'], $data['info_type'], $data['title'], $data['description'], $data['cover']);
        }
        
    }

}