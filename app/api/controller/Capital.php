<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\CapitalFlow as CapitalFlowModel;

class Capital extends Api
{
    protected $CapitalFlowModel;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['except' => 'lists']
    ];
    function __construct()
    {
        parent::__construct();
        $this->CapitalFlowModel = new CapitalFlowModel();
    }

    public function flow()
    {
        $uid = get_uid();
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1]
        ];

        $rows = 10;
        $order_field = input('order_field', 'update_time', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->CapitalFlowModel->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->CapitalFlowModel->handle($val);
        }
        unset($val);

        return $this->success('success',$lists);
    }


}