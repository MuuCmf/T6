<?php
namespace app\common\model;

/**
 * 权限组用户关系模型
 */
class AuthGroupAccess extends Base
{
  /**
     * 权限组用户数量
     */
    public function memberCount($group_id)
    {
        // 关联查询member表,用户状态为1的用户数量
        return $this->where('group_id', $group_id)
                    ->alias('auth_group_access')
                    ->join('member m', 'm.uid = auth_group_access.uid')
                    ->where('m.status', 1)
                    ->count();
    }
}
