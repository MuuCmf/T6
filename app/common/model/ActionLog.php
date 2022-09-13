<?php
namespace app\common\model;

use think\Model;
use think\facade\Db;

class ActionLog extends Model
{
	/**
	 * 记录行为日志，并执行该行为的规则
	 * @param string $action 行为标识
	 * @param string $model 触发行为的模型名
	 * @param int $record_id 触发行为的记录id
	 * @param int $uid 执行行为的用户id
	 * @return boolean
	 */
	public function add($action = null, $model = null, $record_id = null, $uid = null)
	{	
	    //参数检查
	    if (empty($action) || empty($model) || empty($record_id)) {
	        return '参数不能为空';
	    }
	    if (empty($uid)) {
	        return false;
	    }

	    //查询行为,判断是否执行
	    $action_info = Db::name('action')->where('name', $action)->find();

	    if ($action_info['status'] != 1) {
	        return '该行为被禁用或删除';
	    }

	    //插入行为日志
	    $data['action_id'] = $action_info['id'];
	    $data['uid'] = $uid;
	    $data['action_ip'] = request()->ip();
	    $data['model'] = $model;
	    $data['record_id'] = $record_id;
	    $data['create_time'] = time();

	    //解析日志规则,生成日志备注
	    if (!empty($action_info['log'])) {
	        if (preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)) {
	            $log['user'] = $uid;
	            $log['record'] = $record_id;
	            $log['model'] = $model;
	            $log['time'] = time();
	            $log['data'] = ['user' => $uid, 'model' => $model, 'record' => $record_id, 'time' => time()];
	            
	            
	            if(isset($match[1])){
	            	foreach ($match[1] as $value) {
		                $param = explode('|', $value);
		                
		                if (isset($param[1])) {
		                    $replace[] = call_user_func($param[1], $log[$param[0]]);
		                } else {
		                    $replace[] = $log[$param[0]];
		                }
		            }
	            }
	            
	            $data['remark'] = str_replace($match[0], $replace, $action_info['log']);
	            
	        } else {
	            $data['remark'] = $action_info['log'];
	        }

	    } else {
	        //未定义日志规则，记录操作url
	        $data['remark'] = '操作url：' . $_SERVER['REQUEST_URI'];
	    }
	    $res = $this->save($data);

	    return $res;
	}

}