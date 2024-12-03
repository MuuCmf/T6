<?php

namespace app\common\model;

class Withdraw extends Base
{
    protected $autoWriteTimestamp = true;

    /**
     * @title 获取提现配置
     * @return array
     */
    public function getConfig()
    {
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
