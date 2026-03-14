<?php

namespace app\common\model;

class WechatAutoReply extends Base
{
    /**
     * 回复类型
     */
    public function getTypeStrAttr($value)
    {
        $arr = [0 => '关注公众号', 1 => '关注公众号', 2 => '关键词回复', 3 => '扫码登录'];
        return $arr[$value];
    }

    public function getMsgTypeStrAttr($value)
    {
        $arr = ['text' => '文本', 'news' => '图文', 'image' => '图片', 'voice' => '音频', 'video' => '视频'];
        return $arr[$value];
    }

    public function getStatusStrAttr($value)
    {
        $arr = [0 => '禁用', 1 => '启用'];
        return $arr[$value];
    }

    public function getCreateTimeStrAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getUpdateTimeStrAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }



    /**
     * 判断文本是否唯一
     * @internal
     */
    public function checkUnique($key = 'keyword', $text = '', $id = 0)
    {
        $where = [
            [$key, '=', $text]
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        if ($this->where($where)->count() == 0) {
            return true;
        } else {
            return false;
        }
    }
}
