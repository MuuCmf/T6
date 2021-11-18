<?php
namespace app\articles\model;

use app\articles\model\ArticlesBase as Base;

class ArticlesArticles extends Base 
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    public function edit($data)
    {
    	if(!mb_strlen($data['description'],'utf-8')){
            $data['description'] = msubstr(text($data['content']),0,200);
        }
        if(!isset($data['uid'])) $data['uid'] = is_login();
        if(isset($data['template'])) $detail['template'] = $data['template'];
        
        $detail['content'] = $data['content'];
        
        if($data['id']){
            $res = $this->save($data,$data['id']);
        }else{
            $res = $this->save($data);
        }
        
        return $res;
    }

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
    

}