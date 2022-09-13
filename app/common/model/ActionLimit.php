<?php
namespace app\common\model;

use think\Model;
// 引入框架内置类
use think\facade\Request;
use think\facade\Db;

class ActionLimit extends Model
{

    var $item = [];

    var $code = 1;

    var $url;

    var $msg = '';
    
    var $punish = [
        ['warning','警告并禁止'],
        ['logout_account', '强制退出登陆'],
        ['ban_account', '封停账户'],
        ['ban_ip', '封IP'],
    ];

    protected $autoWriteTimestamp = true;
    

    /**
     * ban_account  封停帐号
     * @param $item
     */
    public function ban_account($item)
    {
        set_user_status($item['uid'], 0);
    }

    public function ban_ip($item,$val)
    {
       //TODO 进行封停IP的操作
    }

    public function warning($item,$val){
        $this->code = 0;
        $this->msg = lang('_OPERATION_IS_FREQUENT_PLEASE_').$val['time_number'].get_time_unit($val['time_unit']).lang('_AND_THEN_');
        $this->url = url();
    }

    public function getList($where){
        $list = $this->where($where)->select()->toArray();
        return $list;
    }

    public function addCheckItem($action = null, $model = null, $record_id = null, $uid = null, $ip = false)
    {
        $this->item[] = array('action' => $action, 'model' => $model, 'record_id' => $record_id, 'uid' => $uid, 'action_ip' => $ip);
        return $this;
    }


    public function check()
    {
        $items = $this->item;
        foreach ($items as &$item) {
            $this->checkOne($item);
        }
        unset($item);
    }

    public function checkOne($item)
    {
        $item['action_ip'] = $item['action_ip'] ? request()->ip() : null;
        foreach ($item as $k => $v) {
            if (empty($v)) {
                unset($item[$k]);
            }
        }
        unset($k, $v);

        $limitList = $this->where('action_list','like','%'.$item['action'].'%')->where('status','=',1)->select();
        $item['action_id'] = Db::name('action')->where('name', $item['action'])->field('id')->find();
        $item['action_id'] = implode($item['action_id']);
        unset($item['action']);

        foreach ($limitList as &$val) {
            $ago = get_time_ago($val['time_unit'], $val['time_number'], time());

            $item['create_time'] = ['egt', $ago];

            $log = Db::name('action_log')->where($item)->order('create_time desc')->select();
            
            if (count($log) >= $val['frequency']) {
                $punishes = explode(',', $val['punish']);
                foreach ($punishes as $punish) {
                    //执行惩罚
                    if (method_exists($this, $punish)) {
                        $this->$punish($item,$val);
                    }
                }
                unset($punish);
            }
        }
        unset($val);
    }

    /**
     * 检查行为限制
     * @param  [type]  $action    [description]
     * @param  [type]  $model     [description]
     * @param  [type]  $record_id [description]
     * @param  [type]  $uid       [description]
     * @param  boolean $ip        [description]
     * @return [type]             [description]
     */
    public function checkActionLimit($action = null, $model = null, $record_id = null, $uid = null, $ip = false)
    {
        $item = [
            'action' => $action, 
            'model' => $model, 
            'record_id' => $record_id, 
            'uid' => $uid, 
            'action_ip' => $ip
        ];

        if(empty($record_id)){
            unset($item['record_id']);
        }

        $this->checkOne($item);

        $return = [];
        if (!$this->code) {
            $return['code'] = $this->code;
            $return['msg'] = $this->msg;
            $return['url'] = $this->url;
        }else{
            $return['code'] = 1;
        }
        
        return $return;
    }

    /**
     * [editData description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function edit($data)
    {
        if($data['id']){
            $res = $this->update($data);
        }else{
            $res = $this->insert($data);
        }
        
        return $res;
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
     * 获取限制
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function getPunishName($key){
        !is_array($key) && $key = explode(',',$key);
        
        $punish = $this->punish;
        $return = array();
        foreach($key as $val){
            foreach($punish as $v){
                if($v[0] == $val){
                    $return[]= $v[1];
                }
            }
        }
        return implode(',',$return);
    }
}















