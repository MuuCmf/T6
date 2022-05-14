<?php
namespace app\common\model;

class Withdraw extends Base{
    protected $autoWriteTimestamp = true;

    /**
     * 生成订单号
     * @return [type] [description]
     */
    public function build_order_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 10);
    }
}