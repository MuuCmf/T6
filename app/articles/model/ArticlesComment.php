<?php
namespace app\articles\model;

use app\common\model\Base;

class ArticlesComment extends Base
{

    /**
     * 步进方法
     * @param $id
     * @param string $field
     * @param int $value
     * @return bool
     */
    public function setStep($id ,$field = 'support' ,$value = 1)
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