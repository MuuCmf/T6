<?php
namespace app\common\model;

use think\Model;

class Action extends Model
{
	/**
     * 新增或更新一个行为
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     */
    public function editAction($data){

        if(empty($data)){
            return false;
        }

        if(!empty($data['action_rule'])){
        	$action_rule = $data['action_rule'];
	        if(!empty($action_rule)){

	            for($i=0;$i<count($action_rule['table']);$i++){
	                $data['rule'][] = [
	                    'table'=>$action_rule['table'][$i],
	                    'field'=>$action_rule['field'][$i],
	                    'rule'=>$action_rule['rule'][$i],
	                    'cycle'=>$action_rule['cycle'][$i],
	                    'max'=>$action_rule['max'][$i],
	                ];
	            }
	        }
        }
        
        if(empty($data['rule'])){
            $data['rule'] ='';
        }else{
            $data['rule'] = serialize($data['rule']);
        }
        
        unset($data['action_rule']);
        /* 添加或新增行为 */
        if(empty($data['id'])){ //新增数据
            
            $res = $this->insert($data); //添加行为
            if(!$res){
                $this->error = lang('_NEW_BEHAVIOR_WITH_EXCLAMATION_');
                return false;
            }
        } else { //更新数据
            $res = $this->update($data); //更新基础内容
            if(!$res){
                $this->error = lang('_UPDATE_BEHAVIOR_WITH_EXCLAMATION_');
                return false;
            }
        }
        //删除缓存
        cache('action_list', null);

        //内容添加或更新完成
        return $data;
    }


    public function getAction($map){
        $result = $this->where($map)->select()->toArray();
        foreach ($result as &$v) {
        	if($v['module']=='' || empty($v['module'])) {
        		//默认系统行为模块名
        		$v['module'] = 'admin';
        	}
        }
        unset($v);
        return $result;
    }

    public function getActionOpt(){
        $result = $this->where(['status'=>1])->field('name,title')->select()->toArray();
        return $result;
    }

    public function getActionName($key){
        !is_array($key) && $key = explode(',',str_replace(array('[',']'),'',$key));
        $return = array();
        foreach($key as $val){
            $return[] = $this->where(['name'=>$val])->value('title');
        }
        
        return implode(',',$return);
    }

    public function getListByPage($map,$order='create_time desc',$field='*',$r=20)
    {
        $list = $this->where($map)->order($order)->field($field)->paginate($r,false,['query'=>request()->param()]);
        return $list;
    }

	

}