<?php
namespace app\common\model;

use think\Model;

class Base extends Model{

    public $status = [
        -1 => '删除', 
        0 => '禁用', 
        1 => '正常', 
        2 => '待审核'
    ];

    /**
     * 编辑/新增数据
     *
     * @param      <type>  $data   The data
     * @return     <type>  ( description_of_the_return_value )
     */
    public function edit($data)
    {
        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            if(isset($data['id'])){
                unset($data['id']);
            }
            $res = $this->save($data);
        }
        if(!empty($this->id)){
            return $this->id;
        }else{
            if (is_object($res)) return  $res->id;
            return $res;
        }
    }

    /**
     * Gets the list by page.
     *
     * @param      <type>   $map    The map
     * @param      string   $order  The order
     * @param      string   $field  The field
     * @param      integer  $r      { parameter_description }
     *
     * @return     <type>   The list by page.
     */
    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        if(is_array($map)){
            $list  = $this->where($map)->order($order)->field($field)->paginate(['list_rows'=>$r, 'query'=>request()->param()], false);
        }else{
            $list  = $this->whereRaw($map)->order($order)->field($field)->paginate(['list_rows'=>$r, 'query'=>request()->param()], false);
        }
        

        return $list;
    }
    /**
     * Gets the data by identifier.
     *
     * @param      integer  $id     The identifier
     *
     * @return     <type>   The data by identifier.
     */
    public function getDataById($id,$field='*')
    {
        if($id>0){
            $data = $this->field($field)->find($id);

            return $data;
        }
        return null;
    }

    /**
     * Gets the data by map.
     *
     * @param      <type>  $map    The map
     * @param      string  $field  The field
     *
     * @return     <type>  The data by map.
     */
    public function getDataByMap($map,$field='*')
    {
        if(is_array($map)){
            $data = $this->where($map)->field($field)->find();
        }else{
            $data = $this->whereRaw($map)->field($field)->find();
        }

        return $data;
       
    }
    /**
     * Gets the list.
     *
     * @param      <type>   $map    The map
     * @param      integer  $limit  The limit
     * @param      string   $order  The order
     * @param      string   $field  The field
     *
     * @return     <type>   The list.
     */
    public function getList($map, $limit=10, $order = 'create_time desc' ,$field = '*')
    {
        if(is_array($map)){
            $list  = $this->where($map)->limit($limit)->order($order)->field($field)->select();
        }else{
            $list  = $this->whereRaw($map)->limit($limit)->order($order)->field($field)->select();
        }

        return $list;
    }
    
    public function getCount($map){
        return $this->where($map)->count();
    }
    public function getAvg($map,$field = 'score'){
        return $this->where($map)->avg($field);
    }

    public function setStatus($ids ,$status){
        $map = [];
        if (is_array($ids)){
            $map[] = ['id' ,'in' ,$ids];
        }else{
            $map[] = ['id' ,'=' ,$ids];
        }
        $data = [
            'status' => $status,
            'update_time' => time()
        ];
        $result = $this->where($map)->update($data);
        if ($result !== false){
            return true;
        }
        return false;
    }

    /**
     * @title 字段递增
     * @param $map
     * @param $field
     * @param int $value
     * @return mixed
     */
    public function setInc($map ,$field ,$value = 1){
        return $this->where($map)->inc($field ,$value);
    }

    /**
     * @title 字段递减
     * @param $map
     * @param $field
     * @param int $value
     * @return mixed
     */
    public function setDec($map ,$field ,$value = 1){
        return $this->where($map)->dec($field ,$value);
    }

    
}