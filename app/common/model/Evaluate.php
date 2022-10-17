<?php
namespace app\common\model;
/**
 * 评价模型
 * Class Evaluate
 * @package app\common\model
 */
class Evaluate extends Base 
{
    protected $autoWriteTimestamp = true;
    
    /**
     * 获取统计数据
     * @return [type] [description]
     */
    public function getStatistical($type,$type_id,$shopid)
    {
        //所有
        $where = [];
        $where[] = ['shopid','=',$shopid];
        $where[] = ['type','=',$type];
        $where[] = ['type_id','=',$type];
        $where[] = ['status','=',1];
        $count['all_num']  = $this->getCount($where);

        //计算平均分
        $avg = $this->getAvg($where,'value');
        if($count['all_num']>0){
            $count['avg'] =  sprintf("%.2f", $avg);
        }else{
            $count['avg'] = 5.00;
        }

        //好评
        $goods_rate_where = $where;
        $goods_rate_where[] = ['value','>=',4];
        $count['goods_num'] = $this->getCount($goods_rate_where);
        //好评率
        if($count['all_num']>0){
            $count['goods_rate'] = sprintf("%.2f", $count['goods_num']/$count['all_num'])*100;
        }else{
            $count['goods_rate'] = 100;
        }

        //中评
        $mediums_num_where = $where;
        $mediums_num_where[] = ['value','in',[2,3]];
        $count['mediums_num'] = $this->getCount($mediums_num_where);

        //差评评数
        $bads_num_where = $where;
        $bads_num_where[] = ['value','<',2];
        $count['bads_num'] = $this->getCount($bads_num_where);

        //嗮图
        $pic_num_where = $where;
        $pic_num_where[] = ['images','<>',''];
        $count['pic_num'] = $this->getCount($pic_num_where);

        return $count;
    }

    /**
     * 查询是否已评价
     */
    public function getThisEvaluate($map)
    {
        $map[] = ['status','=',1];
        $result = $this->where($map)->find();
        if($result){
            if($result['create_time'] + 30*3600 > time() && $result['update_time'] == $result['create_time']){
                $result['edit'] = true;
            }
            return $result;
        }else{
            return 0;
        }
    }
}