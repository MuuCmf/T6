<?php
namespace app\common\model;

class CrontabLog extends Base{
    protected $autoWriteTimestamp = true;

    public static function addLog($params){
        $data = [
            'shopid'    =>  $params['shopid'],
            'cid'       =>  $params['cid'],
            'description'   =>  $params['description'],
            'status'    =>  isset($params['status']) ? $params['status'] : 1,
        ];
        return (new self())->edit($data);
    }
}