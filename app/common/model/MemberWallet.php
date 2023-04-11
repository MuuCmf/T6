<?php

namespace app\common\model;

use think\Exception;
use think\facade\Db;

/**
 * Class MemberWallet 用户钱包
 * @package app\common\model
 */
class MemberWallet extends Base
{

    protected $autoWriteTimestamp = true;
    /**
     * @title 初始化用户钱包
     * @param $uid
     * @param int $shopid
     * @return bool
     */
    public function initWallet($shopid = 0, $uid = 0)
    {
        $map = [
            ['uid', '=', $uid],
            ['shopid', '=', $shopid]
        ];
        $wallet = $this->where($map)->count(); //查询当前用户钱包
        if ($wallet > 0) {
            return false;
        }
        $data = [
            'uid'       =>  $uid,
            'shopid'    =>  $shopid,
            'balance'   =>  0,
            'freeze'    =>  0,
            'revenue'   =>  0
        ];
        $result = $this->edit($data);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @title 收入
     * @param $uid [用户ID]
     * @param $money [入账金额]
     * @param int $shopid [店铺ID]
     * @param bool $revenue [是否写入总收益]
     * @return bool
     */
    public function income($shopid = 0, $uid = 0, $money = 0, $revenue = true)
    {
        // 初始化
        $this->initWallet($shopid, $uid);
        $this->startTrans();
        try {
            $map = [
                ['uid', '=', $uid],
                ['shopid', '=', $shopid]
            ];
            $wallet = $this->where($map)->lock(true)->find(); //查询当前用户钱包
            if (empty(Db::raw("balance + {$money}"))) {
                $wallet->balance = 0;
            } else {
                $wallet->balance = Db::raw("balance + {$money}");
            }

            //计入收益
            if ($revenue) {
                if (empty(Db::raw("revenue + {$money}"))) {
                    $wallet->revenue = 0;
                } else {
                    $wallet->revenue = Db::raw("revenue + {$money}");
                }
            }
            $result = $wallet->save();
            if ($result === false) {
                throw new Exception('数据写入失败');
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @title 支出
     * @param $uid [用户ID]
     * @param $money [入账金额]
     * @param int $shopid [店铺ID]
     * @return bool
     */
    public function spending($shopid = 0, $uid = 0, $money = 0)
    {
        // 初始化
        $this->initWallet($shopid, $uid);
        $this->startTrans();
        try {
            $map = [
                ['uid', '=', $uid],
                ['shopid', '=', $shopid]
            ];
            $wallet = $this->where($map)->lock(true)->find(); //查询当前用户钱包

            //扣除用户余额
            if ($wallet->balance < $money) {
                throw new Exception('用户余额不足');
            }
            $wallet->balance = Db::raw("balance - {$money}");
            $result = $wallet->save();
            if ($result === false) {
                throw new Exception('数据写入失败');
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @title 冻结/解冻钱包金额
     * @param $uid [用户ID]
     * @param $money [入账金额，单位：分]
     * @param int $shopid [店铺ID]
     * @param int $scene [1:冻结，0：解冻]
     * @return bool
     */
    public function freeze($shopid = 0, $uid = 0, $money = 0, $scene = 1)
    {
        $this->initWallet($shopid, $uid);
        $this->startTrans();
        try {
            $map = [
                ['uid', '=', $uid],
                ['shopid', '=', $shopid]
            ];
            $wallet = $this->where($map)->lock(true)->find(); //查询当前用户钱包
            //用户余额是否足够冻结
            if ($scene == 1 && $wallet->balance < $money) {
                throw new Exception('用户可用余额不足');
            }

            if ($scene == 1) {
                // 冻结资金
                $wallet->balance = Db::raw("balance - {$money}");
                $wallet->freeze = Db::raw("freeze + {$money}");
            } else {
                // 解冻资金
                if ($wallet->freeze < $money) throw new Exception('冻结资金不足');
                $wallet->balance = Db::raw("balance + {$money}");
                $wallet->freeze = Db::raw("freeze - {$money}");
            }
            $result = $wallet->save();
            if ($result === false) {
                throw new Exception('数据写入失败');
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 减去冻结资金
     */
    public function minusFreeze($shopid = 0, $uid = 0, $money = 0)
    {
        $this->initWallet($shopid, $uid);
        $this->startTrans();
        try {
            $map = [
                ['uid', '=', $uid],
                ['shopid', '=', $shopid]
            ];
            $wallet = $this->where($map)->lock(true)->find(); //查询当前用户钱包
            // 减冻结资金
            if ($wallet->freeze < $money) {
                throw new Exception('冻结资金不足');
            }
            $wallet->freeze = Db::raw("freeze - {$money}");

            $result = $wallet->save();
            if ($result === false) {
                throw new Exception('钱包写入失败');
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 获取用户钱包数据
     */
    public function getWallet($uid)
    {
        $wallet = $this->where('uid', $uid)->field('balance, freeze, revenue')->find();
        if (!empty($wallet)) {
            $wallet = $wallet->toArray();
            // 计算可用余额
            $able_balance = intval($wallet['balance'] - $wallet['freeze']);
            // 数据转换单位
            $wallet['balance'] = sprintf("%.2f", floatval($wallet['balance'] / 100));
            $wallet['freeze'] = sprintf("%.2f", floatval($wallet['freeze'] / 100));
            $wallet['revenue'] = sprintf("%.2f", floatval($wallet['revenue'] / 100));
            $wallet['able_balance'] = sprintf("%.2f", floatval($able_balance / 100));
        } else {
            $wallet['balance'] = 0;
            $wallet['freeze'] = 0;
            $wallet['revenue'] = 0;
            $wallet['able_balance'] = 0;
        }

        return $wallet;
    }
}
