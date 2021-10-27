<?php
namespace app\common\model;

use think\Model;

class Favorites extends Model
{
    //自动写入创建和更新的时间戳字段
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
        
        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            $res = $this->save($data);
        }
        
        if($res) $res = $this->id;

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
    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        $list  = $this->where($map)->order($order)->field($field)->paginate($r,false,['query'=>request()->param()]);

        return $list;
    }
    /**
     * Gets the data by identifier.
     *
     * @param      integer  $id     The identifier
     *
     * @return     <type>   The data by identifier.
     */
    public function getDataById($id, $field='*')
    {
        if($id>0){
            $data = $this->field($field)->find($id);

            if($data){
                return $data;
            }
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
        $list  = $this->where($map)->limit($limit)->order($order)->field($field)->select();
        
        return $list;
    }

    /**
     * 获取收藏量
     *
     * @param      <type>  $info_id    The information identifier
     * @param      <type>  $info_type  The information type
     *
     * @return     <type>  The favorites.
     */
    public function getFavorites($shopid = 0, $app, $info_id, $info_type)
    {   
        if(!empty($shopid) && $shopid != 0){
            $map[] = ['shopid', '=', $shopid];
        }
        $map[] = ['app', '=', $app];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $count = $this->where($map)->count();

        return $count;
    }

    /**
     * 判断用户是否收藏
     */
    public function yesFavorites($shopid = 0, $app, $uid, $info_id, $info_type)
    {
        if(!empty($shopid) && $shopid != 0){
            $map[] = ['shopid', '=', $shopid];
        }
        $map[] = ['app', '=', $app];
        $map[] = ['uid', '=', $uid];
        $map[] = ['info_id', '=', $info_id];
        $map[] = ['info_type', '=', $info_type];
        $map[] = ['status', '=', 1];
        //判断是否收藏
        $data = $this->getDataByMap($map);
        
        return $data;
    }

}