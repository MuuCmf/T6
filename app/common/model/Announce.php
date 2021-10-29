<?php
namespace app\common\model;

use think\Model;

/******************公告模型******************/
class Announce extends Model
{   
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    
    /**
     * 编辑/新增数据
     *
     * @param      <type>  $data   The data
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function edit($data)
    {
        if(!isset($data['uid'])) $data['uid'] = is_login();

        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            $res = $this->save($data);
        }

        return $res;
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
    public function getListByPage($map, $order='create_time desc', $field='*', $r=20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r);

        return $list;
    }
    /**
     * Gets the data by identifier.
     *
     * @param      integer  $id     The identifier
     *
     * @return     <type>   The data by identifier.
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
     * Gets the data by map.
     *
     * @param      <type>  $map    The map
     * @param      string  $field  The field
     *
     * @return     <type>  The data by map.
     */
    public function getDataByMap($map,$field='*')
    {
        $data = $this->where($map)->field($field)->find();
        if($data){
            return $data;
        }
        
        return null;
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
        $list = $this->where($map)->limit($limit)->order($order)->field($field)->select();
    
        return $list;
    }

    protected $_type = [
        0=> '文字',
        1=> '图片'
    ];

    public $_status  = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '删除',
    ];

    /**
     * 格式化数据
     */
    public function formatData($data)
    {   
        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }

        if(isset($data['type'])){
            $data['type_str'] = $this->_type[$data['type']];
        }
        
        if(isset($data['cover'])){
            $data['cover_src_80'] = get_thumb_image($data['cover'], 80, 80);
            $data['cover_src_120'] = get_thumb_image($data['cover'], 120, 120);
            $data['cover_src_200'] = get_thumb_image($data['cover'], 200, 200);
            $data['cover_src_400'] = get_thumb_image($data['cover'], 400, 400);
        }

        //时间戳格式化
        $data['create_time_str'] = time_format($data['create_time']);
        $data['update_time_str'] = time_format($data['update_time']);
        
        return $data;
    }

    
}