<?php
namespace app\articles\model;

use app\articles\model\ArticlesBase as Base;
use app\articles\logic\Category as CategoryLogic;

class ArticlesCategory extends Base
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
     * 获取分类树
     */
    public function getTree($status = 0)
    {   
        $map[] = ['status', '>=', $status];
        $list = $this->getList($map, 999, 'sort desc,create_time desc');
        $list = $list->toArray();
        $CategoryLogic = new CategoryLogic();
        foreach($list as &$v){
            $v = $CategoryLogic->formatData($v);
        }
        unset($v);

        $list = list_to_tree($list);
        
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