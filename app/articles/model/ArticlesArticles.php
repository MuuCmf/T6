<?php
namespace app\articles\model;

use think\Model;

class ArticlesArticles extends Model {

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

    /**
     * 根据id获取文章数据
     *
     * @param      integer  $id     The identifier
     *
     * @return     <type>   The data by identifier.
     */
    public function getDataById($id)
    {
        if(!empty($id)){

            $map['id'] = $id;

            $data=$this->where($map)->find();
            return $data;
        }
        return null;
    }

    /**
     * Gets the list by page.
     *
     * @param      <type>   $map    The map
     * @param      string   $order  The order
     * @param      string   $field  The field
     * @param      integer  $r      { parameter_description }
     *
     * @return     <type>   The list by page.
     */
    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r,false,['query'=>request()->param()]);

        return $list;
    }

    /**
     * 根据map条件获取数据
     *
     * @param      <type>   $map    The map
     * @param      integer  $limit  The limit
     * @param      string   $order  The order
     *
     * @return     <type>   The list by map.
     */
    public function getListByMap($map, $limit=5,$order = 'create_time desc')
    {
    	$list = $this->where($map)->limit($limit)->order($order)->select();

    	return $list;
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