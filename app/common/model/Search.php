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
}