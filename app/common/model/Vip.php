<?php
namespace app\common\model;

use think\Model;

/**
 * 付费会员模型
 */
class Vip extends Model
{
    public function edit($data)
    {
        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            $res = $this->save($data);
        }

        return $res;
    }
    
    /**
     * Gets the list.
     *
     * @param      <array>   $map    The map
     * @param      integer  $limit  The limit
     * @param      string   $order  The order
     * @param      string   $field  The field
     *
     * @return     <array>   The list.
     */
    public function getList($map, $limit = 10, $order = 'create_time desc' ,$field = '*')
    {
        $list = $this->where($map)->limit($limit)->order($order)->field($field)->select();
        
        return $list;
    }

    /**
     * Gets the list by page.
     *
     * @param      <array>   $map    The map
     * @param      string   $order  The order
     * @param      string   $field  The field
     * @param      integer  $r      { parameter_description }
     *
     * @return     <array>   The list by page.
     */
    public function getListByPage($map, $order = 'create_time desc', $field = '*', $r = 20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r);

        return $list;
    }

    /**
     * Gets the data by identifier.
     *
     * @param      integer  $id     The identifier
     *
     * @return     <array>   The data by identifier.
     */
    public function getDataById($id)
    {
        if($id>0){
            $data=$this->find($id);
            return $data;
        }
        return null;
    }

    /**
     * 数据处理
     */
    public function formatData($data)
    {

        return $data;
    }

}