<?php
namespace app\common\logic;

class Crontab extends Base
{
    protected $cycle = [
        'hour' => '每小时',
        'day' => '每天',
        'week' => '每星期',
        'month' => '每月',
        'minute-n' => 'N分钟',
        'hour-n' => 'N小时',
        'day-n' => 'N天',
    ];

    function formatData($data)
    {
        $data = $this->setTimeAttr($data);
        $data = $this->setCycleAttr($data);
        $data = $this->setStatusAttr($data);
        return $data;
    }

    function setCycleAttr($data)
    {
        switch ($data['cycle']) {
            case 'minute-n':
                $data['cycle_str'] = "每{$data['minute']}分钟";
                break;
            case 'hour':
                $data['cycle_str'] = "每小时{$data['minute']}分钟";
                break;
            case 'day':
                $data['cycle_str'] = "每天{$data['hour']}时{$data['minute']}分";
                break;
            case 'hour-n':
                $data['cycle_str'] = "每{$data['hour']}小时{$data['minute']}分";
                break;
            case 'month':
                $data['cycle_str'] = "每月{$data['day']}日{$data['hour']}时{$data['minute']}分";
                break;
            case 'day-n':
                $data['cycle_str'] = "每{$data['day']}天{$data['hour']}时{$data['minute']}分";
                break;
        }
        return $data;
    }
}