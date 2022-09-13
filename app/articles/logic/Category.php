<?php
namespace app\articles\logic;

/*
 * 分类数据逻辑层
 */
class Category {

    /**
     * 知识分类状态
     */
    public $_status = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '已删除'
    ];

    /**
	 * 是否父级分类
	 * @param  [type] $category_id [description]
	 * @return [type]              [description]
	 */
	public function yesParent($category_id)
	{
        $map[] = ['pid', '=', $category_id];
		$category_data = (new \app\articles\model\ArticlesCategory)->getList($map, 999)->toArray();

		$cates_arr = [];
		if(!empty($category_data)){
			$cates_arr = array_column($category_data,'id');
			$cates_arr = array_merge(array($category_id),$cates_arr);
		}
		return $cates_arr;
	}

    /**
     * 格式化数据
     *
     * @param      <type>  $data   The data
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function formatData($data)
    {   
        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }

        
        $data['cover_100'] = get_thumb_image($data['cover'], 100, 100);
        $data['cover_200'] = get_thumb_image($data['cover'], 200, 200);
        $data['cover_400'] = get_thumb_image($data['cover'], 400, 400);
        
        //时间戳格式化
        if(isset($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
        }
        if(isset($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
        }

        return $data;
    }

    /**
     * 列表转树结构
     */
    public function categoryTree($list)
    {   
        foreach($list as &$v){
            $v = $this->formatData($v);
        }
        unset($v);
        
        $tree = list_to_tree($list);
        
        return $tree;
    }

}