<?php

namespace app\common\model;

use think\facade\Db;
use think\Helper\Str;

class ActionLimit extends Base
{
    protected $autoWriteTimestamp = true;

    var $item = [];

    var $code = 1;

    var $url;

    var $msg = '';

    var $punish = [
        ['warning', '警告并禁止'],
        ['logoutAccount', '强制退出登陆'],
        ['banAccount', '封停账户'],
        ['banIp', '封IP'],
    ];

    /**
     * ban_account  封停帐号
     * @param $item
     */
    public function banAccount($item)
    {
        //TODO 禁用账号
    }

    public function banIp($item, $val)
    {
        //TODO 进行封停IP的操作
    }

    public function warning($item, $val)
    {
        $this->code = 0;
        $this->msg = lang('_OPERATION_IS_FREQUENT_PLEASE_') . $val['time_number'] . get_time_unit($val['time_unit']) . lang('_AND_THEN_');
        $this->url = url();
    }

    public function logoutAccount($item, $val)
    {
        //TODO 强制退出登陆
    }

    public function addCheckItem($action = null, $model = null, $record_id = null, $uid = null, $ip = false)
    {
        $this->item[] = array('action' => $action, 'model' => $model, 'record_id' => $record_id, 'uid' => $uid, 'action_ip' => $ip);
        return $this;
    }

    /**
     * 检查动作限制
     * 遍历所有限制项目并逐一进行检查
     * 
     * @return void
     */
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

        $limitList = $this->where('action_list', 'like', '%' . $item['action'] . '%')->where('status', '=', 1)->select();
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
                    $punish = Str::camel($punish);
                    if (method_exists($this, $punish)) {
                        $this->$punish($item, $val);
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

        if (empty($record_id)) {
            unset($item['record_id']);
        }

        $this->checkOne($item);

        $return = [];
        if (!$this->code) {
            $return['code'] = $this->code;
            $return['msg'] = $this->msg;
            $return['url'] = $this->url;
        } else {
            $return['code'] = 1;
        }

        return $return;
    }

    /**
     * 获取限制
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function getPunishName($key)
    {
        !is_array($key) && $key = explode(',', $key);

        $punish = $this->punish;
        $return = array();
        foreach ($key as $val) {
            foreach ($punish as $v) {
                if ($v[0] == $val) {
                    $return[] = $v[1];
                }
            }
        }
        return implode(',', $return);
    }
}
