<?php

namespace app\common\model;

class Search extends Base
{
    protected $autoWriteTimestamp = true;

    /**
     * 写入数据
     */
    public function add($shopid, $app, $info_id, $info_type, $title, $description, $cover = '')
    {
        // 查询是否已写入
        $map = [
            ['shopid', '=', $shopid],
            ['app', '=', $app],
            ['info_id', '=', $info_id],
            ['info_type', '=', $info_type],
        ];

        $has_data = $this->where($map)->find();

        // 初始化写入数据
        $data['shopid'] = $shopid;
        $data['app'] = $app;
        $data['info_id'] = $info_id;
        $data['info_type'] = $info_type;
        $content = [
            'title' => $title,
            'description' => $description,
            'cover' => $cover
        ];
        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $data['content'] = $content;

        // 判断是写入或更新
        if (!empty($has_data)) {
            $data['id'] = $has_data['id'];
            $res = $this->update($data);
        } else {
            $res = $this->save($data);
        }
        if (!empty($this->id)) {
            return $this->id;
        } else {
            if (is_object($res)) return  $res->id;
            return $res;
        }
    }

    /**
     * 单条数据处理
     */
    public function handle($data)
    {
        if(!empty($data['content'])){
            $data['content'] = json_decode($data['content'], true);
            $cover = $data['content']['cover'];
            if(!empty($cover)){
                //处理缩微图
                $cover_arr['cover_200'] = get_thumb_image($cover, 200, 200);
                $cover_arr['cover_300'] = get_thumb_image($cover, 300, 300);
                $cover_arr['cover_400'] = get_thumb_image($cover, 400, 400);
                $cover_arr['cover_original'] = get_attachment_src($cover);

                $data['content'] = array_merge($data['content'], $cover_arr);
            }
        }
        if(!empty($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
            $data['create_time_friendly_str'] = friendly_date($data['create_time']);
        }
        if(!empty($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
            $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        }

        return $data;
    }
}