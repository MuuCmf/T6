<?php
namespace app\demo\controller;

use think\Exception;
use think\facade\Db;
use think\Request;
use think\facade\Log;
use app\common\controller\Api;
use app\channel\facade\channel\Channel as ChannelServer;
use app\channel\facade\channel\Pay as PayServer;
use app\common\model\CapitalFlow;
use app\common\model\CapitalFlow as CapitalFlowModel;
use app\common\model\MemberWallet;
use app\common\model\Withdraw as WithdrawModel;
use app\common\logic\Withdraw as WithdrawLogic;
use app\common\controller\Common;

class Debug extends Api
{
    function __construct(Request $request)
    {
        parent::__construct();
        //中间件加载完成后执行
        $this->WithdrawModel = new WithdrawModel();
        $this->WithdrawLogic = new WithdrawLogic();
        $this->CapitalFlowModel = new CapitalFlowModel();
    }

    public function index2(){

        dump(request()->host());
    }
    public function index()
    {
        $price = 1;
        $price = intval($price * 100); // 单位转为分
        $channel = input('channel', 'weixin_mp', 'text');
        $pay_channel = input('pay_channel', 'weixin', 'text');
        $uid = 56;

        $result = [
            'return_code' => 'SUCCESS',
            'result_code' => 'SUCCESS'
        ];
        //Db::startTrans();
        try {
            $config = $this->WithdrawModel->getConfig();//获取提现配置
            //是否开启提现
            if ($config['status'] < 1) throw new Exception('提现暂时关闭，如有特殊需求请联系客服');
            //初始化提现数据
            $data['shopid'] =   $this->shopid;
            $data['uid']    =   $uid;
            $data['price']  =   $price;
            $data['order_no']   =   build_order_no();//生成提现单号
            $data['channel']    =   $channel;
            $data['pay_channel'] = $pay_channel;
            $data['error']  =   0;
            $data['paid']  =   0;
            //扣除平台手续费后，实际到账金额
            $rate = floatval($config['tax_rate']) / 1000;
            $deduct_money = intval($data['price'] * $rate);
            $data['real_price'] = intval(ceil(($data['price'] - $deduct_money)));//单位分
            //最低金额
            if ($data['price'] < intval($config['min_price']) * 100) throw new Exception('提现金额最少为' . $config['min_price'] . '元');
            //最大金额
            if ($data['price'] > intval($config['max_price']) * 100) throw new Exception('提现金额最多为' . $config['max_price'] . '元');
            //查询今日提现次数
            $today_unixtime = dayTime();//今日时间戳
            $check_map = [
                ['shopid' ,'=' ,$this->shopid],
                ['uid' ,'=' ,$uid],
                ['create_time' ,'between' ,[$today_unixtime[0],$today_unixtime[1]]]
            ];
            $withdraw_order_total = $this->WithdrawModel->where($check_map)->count();
            if ($withdraw_order_total >= $config['day_num']) throw new Exception('每日最多可提现' . $config['day_num'] . '次');
            //获取用户openid
            $openid = get_openid($uid ,$channel);
            if (!$openid)   throw new Exception('用户不存在');
            //用户钱包模型
            $WalletModel = new MemberWallet();
            $wallet = $WalletModel->where('uid',$uid)->find()->toArray();
            if (!intval($wallet['balance'] - $wallet['freeze']) >= $data['price']) {
                throw new Exception('账户余额不足');
            }
            //冻结资金
            $WalletModel->freeze($this->shopid, $uid, $data['price']);
            //提现记录
            $withdraw_id = $this->WithdrawModel->edit($data);
            if (!$withdraw_id)  throw new Exception('网络异常，请稍后再试');
            // // 发起提现
            // $result = $this->PayService->server->toBalance([
            //     'check_name' => 'NO_CHECK',
            //     'partner_trade_no'  =>  $data['order_no'],
            //     'openid'    =>  $openid,
            //     'amount'    =>  $data['real_price'],
            //     'desc'      =>  '提现'
            // ]);
            // 记录日志
            
            //Log::write($result,'notice');
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                //扣除冻结余额
                (new MemberWallet())->minusFreeze($data['shopid'], $data['uid'] ,$data['price']);

                //写入资金流水表
                $result_capital_flow = (new CapitalFlow())->createFlow([
                    'uid' => $data['uid'],
                    'order_no' => $data['order_no'],
                    'price' => $data['price'],
                    'shopid' => $data['shopid'],
                    'app' => 'system',
                    'channel' => $data['channel'],
                ]);
                if (!$result_capital_flow)  throw new Exception('写入资金流失失败');

                //更改提现记录状态
                $submit_data = [
                    'id'        => $withdraw_id,
                    'paid'      => 1,
                    'paid_time' => time(),
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            }else{
                //解冻冻结资金
                $WalletModel->freeze($data['shopid'], $data['uid'], $data['price'], 0);
                //付款到零钱失败
                $submit_data = [
                    'id'     => $withdraw_id,
                    'error'  => 1,
                    'error_msg'  => json_encode($result)
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            }
            if (!$cash_with){
                throw new Exception('网络异常,请稍后再试');
            }
            //Db::commit();
            //dump($cash_with);exit;
            //return $this->error('error');
            return $this->success('提现已提交，正在处理...');
            dump($cash_with);exit;
        }catch (\Exception $e){
            //Db::rollback();
            return $this->error($e->getMessage());
        }
    }


}