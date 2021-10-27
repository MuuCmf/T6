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
    public $logic;//处理类
    public $statusMap = [-1 => '删除', 0 => '禁用', 1 => '正常', 2 => '待审核'];
    public function getStatusStrAttr($value)
    {
        if (is_numeric($value)){
            return $this->statusMap[$value];
        }
        return $value;
    }
    /**
     * [editData description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function edit($data)
    {
        if(!empty($data['id'])){
            $res = $this->update($data);
            if ($res !== false){
                $res = $data['id'];
            }
        }else{
            if (isset($data['id'])){
                unset($data['id']);
            }
            $res = $this->save($data);
            if ($res){
                $res = $this->id;
            }
        }
        return $res;
    }
    public function getListByPage($map, $order='create_time desc', $field='*', $r=20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r);
        return $list;
    }
    public function getDataById($id)
    {
        if($id>0){
            $data = $this->find($id);
            if ($data){
                $data = $data->toArray();
                if (is_object($this->logic)){
                    $data = $this->logic->_formatData($data);
                }
            }
            return $data;
        }
        return null;
    }

    public function getDataByMap($map)
    {
        $data = $this->where($map)->find();
        if ($data){
            $data = $data->toArray();
            if (is_object($this->logic)){
                $data = $this->logic->_formatData($data);
            }
        }
        return $data;
    }

    public function getList($map, $limit=10, $order = 'create_time desc' ,$field = '*')
    {
        $list = $this->where($map)->limit($limit)->order($order)->field($field)->select();
        if ($list){
            $list = $list->toArray();
            if (is_object($this->logic)){
                foreach ($list as &$val){
                    $val = $this->logic->_formatData($val);
                }
                unset($val);
            }
        }

        return $list;
    }
    public function getCount($map){
        return $this->where($map)->count();
    }
    public function getAvg($map,$field = 'score'){
        return $this->where($map)->avg($field);
    }
}