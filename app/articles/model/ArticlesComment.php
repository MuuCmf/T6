<?php
namespace app\articles\model;

use think\Model;

class ArticlesComment extends Model
{

    public function edit($data)
    {
        $d = $this->get($data['articles_id']);
        if($d){
            $res=$this->save($data);
        }else{
            $res=$this->save($data);
        }
        return $res;
    }

    public function getDataById($id)
    {   
        $map['articles_id'] = $id;
        $res=$this->get($map);
        return $res;
    }

}