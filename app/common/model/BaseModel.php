<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: BaseModel.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/29
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\model;
use think\Model;

class BaseModel extends Model{
    public $statusMap = [-1 => '删除', 0 => '禁用', 1 => '正常', 2 => '待审核'];
    public function getStatusStrAttr($value)
    {
        return $this->statusMap[$value];
    }
    /**
     * [editData description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function edit($data)
    {
        if($data['id']){
            $res = $this->update($data);
        }else{
            $res = $this->insert($data);
        }

        return $res;
    }
}