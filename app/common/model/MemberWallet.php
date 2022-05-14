<?php
namespace app\common\model;

use think\Exception;
use think\facade\Db;

/**
 * Class MemberWallet 用户钱包
 * @package app\common\model
 */
class MemberWallet extends Base{


    /**
     * @title 初始化用户钱包
     * @param $uid
     * @param int $shopid
     * @return bool
     */
    public function initWallet($uid ,$shopid = 0){
        $map = [
            ['uid' ,'=' ,$uid],
            ['shopid' ,'=' ,$shopid]
        ];
        $wallet = $this->where($map)->count();//查询当前用户钱包
        if ($wallet > 0){
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
        if ($result){
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
    public function income($uid ,$money ,$shopid = 0 ,$revenue = true){
        $this->startTrans();
        $this->initWallet($uid ,$shopid);
        $map = [
            ['uid' ,'=' ,$uid],
            ['shopid' ,'=' ,$shopid]
        ];
        $wallet = $this->where($map)->find();//查询当前用户钱包
        $wallet->balance = Db::raw("balance + {$money}");
        //计入收益
        if ($revenue) $wallet->revenue = Db::raw("revenue + {$money}");
        $result = $wallet->save();
        if ($result !== false){
            $this->commit();
            return true;
        }
        $this->rollback();
        throw new Exception('钱包写入失败');
    }

    /**
     * @title 支出
     * @param $uid [用户ID]
     * @param $money [入账金额]
     * @param int $shopid [店铺ID]
     * @param bool $freeze [是否冻结]
     * @return bool
     */
    public function spending($uid ,$money ,$shopid = 0 ,$freeze = true){
        $this->startTrans();
        try {
            $this->initWallet($uid ,$shopid);
            $map = [
                ['uid' ,'=' ,$uid],
                ['shopid' ,'=' ,$shopid]
            ];
            $wallet = $this->where($map)->lock(true)->find();//查询当前用户钱包

            //扣除用户余额
            if ($wallet->balance < $money){
                throw new Exception('用户余额不足');
            }
            $wallet->balance = Db::raw("balance - {$money}");
            //扣除冻结金额
            if ($freeze && $wallet->freeze < $money){
                throw new Exception('冻结资金不足');
            }
            $wallet->freeze = Db::raw("freeze - {$money}");

            $result = $wallet->save();
            if ($result === false){
                throw new Exception('钱包写入失败');
            }
            $this->commit();
            return true;
        }catch (Exception $e){
            $this->rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @title 冻结钱包金额
     * @param $uid [用户ID]
     * @param $money [入账金额]
     * @param int $shopid [店铺ID]
     * @return bool
     */
    public function freeze($uid ,$money ,$shopid = 0 ,$type = 1){
        $this->startTrans();
        try {
            $this->initWallet($uid ,$shopid);
            $map = [
                ['uid' ,'=' ,$uid],
                ['shopid' ,'=' ,$shopid]
            ];
            $wallet = $this->where($map)->lock(true)->find();//查询当前用户钱包
            //用户余额是否足够冻结
            if ($type == 1 && $wallet->balane < $money){
                throw new Exception('用户余额不足,无法冻结');
            }

            if ($type == 1){
                $wallet->freeze = Db::raw("freeze + {$money}");
            }else{
                if ($wallet->freeze < $money) throw new Exception('冻结资金不足,无法恢复余额');
                $wallet->freeze = Db::raw("freeze - {$money}");
            }
            $result = $wallet->save();
            if ($result === false){
                throw new Exception('钱包写入失败');
            }
            $this->commit();
            return true;
        }catch (Exception $e){
            $this->rollback();
            throw new Exception($e->getMessage());
        }
    }

}