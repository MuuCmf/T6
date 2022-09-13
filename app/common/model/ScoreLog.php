<?php
namespace app\common\model;

use think\facade\Db;
/**
 * 用户积分日志模型
 */
class ScoreLog extends Base
{
    
    /**
     * 添加积分日志
     * @param [type]  $uid       [description]
     * @param [type]  $type      [description]
     * @param string  $action    [description]
     * @param integer $value     [description]
     * @param string  $model     [description]
     * @param integer $record_id [description]
     * @param string  $remark    [description]
     */
    public function addScoreLog($uid, $type, $action = 'inc',$value = 0, $model='', $record_id = 0,$remark = '')
    {
        $uid = is_array($uid) ? $uid : explode(',',$uid);
        foreach($uid as $v){
            $score =  Db::name('Member')->where(['uid'=>$v])->value('score'.$type);
            
            $data['uid'] = $v;
            $data['ip'] = request()->ip(1);
            $data['type'] = $type;
            $data['action'] = $action;
            $data['value'] = $value;
            $data['model'] = $model;
            $data['record_id'] = $record_id;
            $data['finally_value'] = $score;
            $data['remark'] = $remark;
            $data['create_time'] = time();
            $this->insert($data);
        }

        return true;
    }
}