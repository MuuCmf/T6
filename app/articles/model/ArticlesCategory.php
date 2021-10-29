<?php
namespace app\articles\model;

use think\Model;
use app\articles\logic\Category as CategoryLogic;

class ArticlesCategory extends Model
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
        if(!isset($data['uid'])) $data['uid'] = is_login();
        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            $res = $this->save($data);
        }

        return $res;
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
        if($id > 0){
            $data=$this->find($id);
            return $data;
        }
        return null;
    }

    public function getDataTitleById($id)
    {
        
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
    public function getList($map, $limit=999, $order = 'sort desc,create_time desc' , $field = '*')
    {
        $list = $this->where($map)->limit($limit)->order($order)->field($field)->select();
        
        return $list;
    }
    
    /**
     * 获取分页列表
     */
    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        $list  = $this->where($map)->order($order)->field($field)->paginate($r,false,['query'=>request()->param()]);

        foreach($list as &$v){
            $v = $this->_formatData($v);
        }
        unset($v);

        return $list;
    }

    public function category($data){
        //获取分类数据
        if(!empty($data['category_id'])){
            $data['category'] = $this->getDataById($data['category_id']);
        }

        return $data;
    }

    /**
	 * 是否父级分类
	 * @param  [type] $category_id [description]
	 * @return [type]              [description]
	 */
	public function yesParent($category_id)
	{
        $map[] = ['pid', '=', $category_id];
		$category_data = $this->getList($map, 999);

		$cates_arr = [];
		if(!empty($category_data)){
			$cates_arr = array_column($category_data,'id');
			$cates_arr = array_merge(array($category_id),$cates_arr);
		}
		return $cates_arr;
	}

    
}