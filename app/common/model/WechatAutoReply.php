<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: WechatAutoReply.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/29
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */

namespace app\common\model;
class WechatAutoReply extends BaseModel
{
    public function getTypeStrAttr($value)
    {
        $arr = ['关注回复','文本'];
        return $arr[$value];
    }
    /**
     * 判断文本是否唯一
     * @internal
     */
    public function checkUnique($key = 'keyword',$text = '',$id = 0)
    {
        $where = [
            [$key,$text]
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