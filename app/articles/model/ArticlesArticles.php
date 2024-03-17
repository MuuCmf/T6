<?php
namespace app\articles\model;

use app\common\model\Base;

class ArticlesArticles extends Base 
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    //获取用户文章数的总阅读量
    public function _totalView(int $author_id)
    {
        $total = $this->where(['author_id'=>$author_id])->sum('view');

        return $total;
    }

    /**
     * 步进方法
     * @param $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function setStep(int $id , string $field , int $value = 1)
    {
        // 查询值是否为0
        $count = $this->where('id', $id)->value($field);

        // 增加
        if($value > 0){
            $res = $this->where('id',$id)->inc($field, $value)->update();
            if ($res !== false){
                return true;
            }
        }
        // 减少
        if($value < 0 && $count > 0){
            $res = $this->where('id',$id)->inc($field, $value)->update();
            if ($res !== false){
                return true;
            }
        }
        
        return  false;
    }

    /**
     * 获取上一篇数据
     */
    public function getNextDetail($shopid, $id)
    {
        $lists = $this->where([
            ['shopid', '=', $shopid],
            ['status', '=', 1],
            ['id', '>', $id]
        ])
        ->order('id', 'asc')
        ->field('id,title,description')
        ->limit(1)
        ->select();
        
        if(!empty($lists[0])){
            return $lists[0];
        }
        
        return false;
    }

    /**
     * 获取下一篇数据
     */
    public function getPrevDetail($shopid, $id)
    {
        $lists = $this->where([
            ['shopid', '=', $shopid],
            ['status', '=', 1],
            ['id', '<', $id]
        ])
        ->order('id', 'desc')
        ->field('id,title,description')
        ->limit(1)
        ->select();

        if(!empty($lists[0])){
            return $lists[0];
        }
        
        return false;
    }
}