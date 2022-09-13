<?php
namespace app\articles\model;

use app\articles\model\ArticlesBase as Base;

class ArticlesArticles extends Base 
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    //获取用户文章数的总阅读量
    public function _totalView($uid=0)
    {
        $total = cache("article_total_view_uid_{$uid}");
        if(!$total){
            $res = $this->where(['uid'=>$uid])->select();
            $total=0;
            foreach($res as $value){ 
                $total = $total+$value['view'];
            }
            unset($value);
            cache("article_total_view_uid_{$uid}",$total,3600);
        }
        return $total;
    }

    /**
     * 步进方法
     * @param $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function setStep($id ,$field = 'view' ,$value = 1)
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
}