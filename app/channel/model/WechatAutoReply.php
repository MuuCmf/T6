<?php
namespace app\channel\model;

use app\common\model\Base;

class WechatAutoReply extends Base
{
    public function getTypeStrAttr($value)
    {
        $arr = [1=>'关注回复',2=>'自动回复',3 =>'扫码登录'];
        return $arr[$value];
    }
    public function getMsgTypeStrAttr($value)
    {
        $arr = ['text' => '文本' ,'news'=>'图文', 'image' => '图片', 'voice' => '音频' ,'video' => '视频'];
        return $arr[$value];
    }
    /**
     * 判断文本是否唯一
     * @internal
     */
    public function checkUnique($key = 'keyword',$text = '',$id = 0)
    {
        $where = [
            [$key,'=',$text]
        ];
        if ($id){
            $where[] = ['id','<>',$id];
        }
        if ($this->where($where)->count() == 0) {
            return true;
        } else {
            return false;
        }
    }
}