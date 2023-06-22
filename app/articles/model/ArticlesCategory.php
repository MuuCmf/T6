<?php
namespace app\articles\model;

use app\common\model\Base;
use app\articles\logic\Category as CategoryLogic;

class ArticlesCategory extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true;

    /**
     * 获取分类树
     */
    public function tree(int $shopid, $status)
    {   
        $map[] = ['shopid', '=', $shopid];
        if(is_array($status)){
            $map[] = ['status', 'in', $status];
        }else{
            $map[] = ['status', '=', $status];
        }

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

    public function category($data)
    {
        //获取分类数据
        if(!empty($data['category_id'])){
            $data['category'] = $this->getDataById($data['category_id']);
        }

        return $data;
    }

    
}