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

    /**
     * @title 获取提现配置
     * @return array
     */
    public function getConfig(){
        $config = [
            'status' => config('extend.WITHDRAW_STATUS'),
            'tax_rate' => config('extend.WITHDRAW_TAX_RATE'),
            'day_num' => config('extend.WITHDRAW_DAY_NUM'),
            'min_price' => config('extend.WITHDRAW_MIN_PRICE'),
            'max_price' => config('extend.WITHDRAW_MAX_PRICE'),
        ];
        return $config;
    }
}