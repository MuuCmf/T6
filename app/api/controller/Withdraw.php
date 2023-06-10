<?php

namespace app\api\controller;

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

class Withdraw extends Api
{
    protected $WithdrawModel; //订单模型
    protected $WithdrawLogic; //订单模型
    protected $CapitalFlowModel;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    function __construct(Request $request)
    {
        parent::__construct();
        $this->WithdrawModel = new WithdrawModel();
        $this->WithdrawLogic = new WithdrawLogic();
        $this->CapitalFlowModel = new CapitalFlowModel();
    }

    /**
     * @title 提现
     */
    public function withdraw()
    {
        $uid = get_uid();
        $price = input('price', '', 'text');
        $price = floatval($price);
        $price = intval($price * 100); // 单位转为分
        $channel = input('channel', 'weixin_h5', 'text');
        $pay_channel = input('pay_channel', 'weixin', 'text');
        
        Db::startTrans();
        try {
            $config = $this->WithdrawModel->getConfig(); //获取提现配置
            //是否开启提现
            if ($config['status'] < 1) throw new Exception('提现暂时关闭，如有特殊需求请联系客服');
            //初始化提现数据
            $data['shopid'] =   $this->shopid;
            $data['uid']    =   $uid;
            $data['price']  =   $price;
            $data['order_no']   =   build_order_no(); //生成提现单号
            $data['channel']    =   $channel;
            $data['pay_channel'] = $pay_channel;
            $data['error']  =   0;
            $data['paid']  =   0;

            //获取用户信息
            $user_info = query_user($uid);
            if($user_info == -1) throw new Exception('用户数据不存在');

            //扣除平台手续费后，实际到账金额
            $rate = floatval($config['tax_rate']) / 1000;
            $deduct_money = intval($data['price'] * $rate);
            $data['real_price'] = intval(ceil(($data['price'] - $deduct_money))); //单位分
            //最低金额
            if ($data['price'] < intval($config['min_price']) * 100) throw new Exception('提现金额最少为' . $config['min_price'] . '元');
            //最大金额
            if ($data['price'] > intval($config['max_price']) * 100) throw new Exception('提现金额最多为' . $config['max_price'] . '元');

            //查询今日提现次数
            $today_unixtime = dayTime(); //今日时间戳
            $check_map = [
                ['shopid', '=', $this->shopid],
                ['uid', '=', $uid],
                ['create_time', 'between', [$today_unixtime[0], $today_unixtime[1]]]
            ];
            $withdraw_order_total = $this->WithdrawModel->where($check_map)->count();
            if ($withdraw_order_total >= $config['day_num']) throw new Exception('每日最多可提现' . $config['day_num'] . '次');

            //获取用户openid
            $openid = get_openid($this->shopid, $uid, $channel);
            if (!$openid)   throw new Exception('用户不存在');

            //用户钱包模型
            $WalletModel = new MemberWallet();
            $wallet = $WalletModel->where('uid', $uid)->find()->toArray();
            if (intval($wallet['balance'] - $wallet['freeze']) < $data['price']) {
                throw new Exception('账户余额不足');
            }
            //冻结资金
            $WalletModel->freeze($this->shopid, $uid, $data['price']);

            //提现记录
            $withdraw_id = $this->WithdrawModel->edit($data);
            if (!$withdraw_id)  throw new Exception('网络异常，请稍后再试');

            // 发起提现
            $pay_config = ChannelServer::config($channel, $this->shopid);
            $PayService = PayServer::init($pay_config['appid'], $pay_channel, $this->shopid);
            // 提现接口
            $withdraw_api = config('extend.WX_PAY_WITHDRAW_API');
            if($withdraw_api == 'v2'){
                $result = $PayService->server->toBalance([
                    'check_name' => 'NO_CHECK',
                    'partner_trade_no'  =>  $data['order_no'],
                    'openid'    =>  $openid,
                    'amount'    =>  $data['real_price'],
                    'desc'      =>  '提现'
                ]);
            }

            if($withdraw_api == 'v3'){
                $result = $PayService->server->toBalanceV3([
                    'appid'                 => $pay_config['appid'],
                    'out_batch_no'          => $data['order_no'], //商户系统内部的商家批次单号，要求此参数只能由数字、大小写字母组成，在商户系统内部唯一,
                    'batch_name'            => '用户提现',       //该笔批量转账的名称
                    'batch_remark'          => 'uid:' . $data['uid'] . "-" . '提现', //转账说明，UTF8编码，最多允许32个字符
                    'total_amount'          => $data['price'], //转账总金额 单位为“分”
                    'total_num'             => 1,
                    'transfer_detail_list'  => [
                        [
                            'out_detail_no'     => $data['order_no'],
                            'transfer_amount'   => $data['price'],
                            'transfer_remark'   => $user_info['nickname'] . '(uid:' . $data['uid'] . ')' . '主动提现',
                            'openid'            => $openid,
                            //'user_name'         => $encryptor($params['name']) // 金额超过`2000`才填写
                        ]
                    ]
                ]);

                if(is_array($result) && isset($result['errCode']) && $result['errCode'] == 0) throw new Exception($result['errMsg']);
            }
            
            // 记录日志
            Log::write($result, 'notice');
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                //扣除冻结余额
                (new MemberWallet())->minusFreeze($data['shopid'], $data['uid'], $data['price']);

                //写入资金流水表
                $result_capital_flow = (new CapitalFlow())->createFlow([
                    'uid' => $data['uid'],
                    'order_no' => $data['order_no'],
                    'price' => $data['price'],
                    'shopid' => $data['shopid'],
                    'app' => 'system',
                    'channel' => $data['channel'],
                    'remark' => '用户提现',
                ]);
                if (!$result_capital_flow)  throw new Exception('写入资金流水失败');

                //更改提现记录状态
                $submit_data = [
                    'id'        => $withdraw_id,
                    'paid'      => 1,
                    'paid_time' => time(),
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            } else {
                //解冻冻结资金(返还至用户余额)
                $WalletModel->freeze($data['shopid'], $data['uid'], $data['price'], 0);
                //付款到零钱失败
                $submit_data = [
                    'id'     => $withdraw_id,
                    'error'  => 1,
                    'error_msg'  => json_encode($result)
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            }
            if (!$cash_with) {
                throw new Exception('网络异常,请稍后再试');
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $this->error('发生错误：' . $e->getMessage());
        }

        return $this->success('提现已提交，正在处理...');
    }

    /**
     * DEBUG
     */
    public function v3()
    {
        $uid = 256;
        $price = '1';
        $price = intval($price * 100); // 单位转为分
        $channel = input('channel', 'weixin_h5', 'text');
        $pay_channel = input('pay_channel', 'weixin', 'text');
        Db::startTrans();
        try {
            $config = $this->WithdrawModel->getConfig(); //获取提现配置
            //是否开启提现
            if ($config['status'] < 1) throw new Exception('提现暂时关闭，如有特殊需求请联系客服');
            //初始化提现数据
            $data['shopid'] =   $this->shopid;
            $data['uid']    =   $uid;
            $data['price']  =   $price;
            $data['order_no']   =   build_order_no(); //生成提现单号
            $data['channel']    =   $channel;
            $data['pay_channel'] = $pay_channel;
            $data['error']  =   0;
            $data['paid']  =   0;
            //扣除平台手续费后，实际到账金额
            $rate = floatval($config['tax_rate']) / 1000;
            $deduct_money = intval($data['price'] * $rate);
            $data['real_price'] = intval(ceil(($data['price'] - $deduct_money))); //单位分
            //最低金额
            if ($data['price'] < intval($config['min_price']) * 100) throw new Exception('提现金额最少为' . $config['min_price'] . '元');
            //最大金额
            if ($data['price'] > intval($config['max_price']) * 100) throw new Exception('提现金额最多为' . $config['max_price'] . '元');
            //查询今日提现次数
            $today_unixtime = dayTime(); //今日时间戳
            $check_map = [
                ['shopid', '=', $this->shopid],
                ['uid', '=', $uid],
                ['create_time', 'between', [$today_unixtime[0], $today_unixtime[1]]]
            ];
            $withdraw_order_total = $this->WithdrawModel->where($check_map)->count();
            if ($withdraw_order_total >= $config['day_num']) throw new Exception('每日最多可提现' . $config['day_num'] . '次');
            //获取用户openid
            $openid = get_openid($this->shopid, $uid, $channel);
            if (!$openid)   throw new Exception('用户不存在');
            //用户钱包模型
            $WalletModel = new MemberWallet();
            $wallet = $WalletModel->where('uid', $uid)->find()->toArray();
            if (intval($wallet['balance'] - $wallet['freeze']) < $data['price']) {
                throw new Exception('账户余额不足');
            }
            //冻结资金
            $WalletModel->freeze($this->shopid, $uid, $data['price']);
            //提现记录
            $withdraw_id = $this->WithdrawModel->edit($data);
            if (!$withdraw_id)  throw new Exception('网络异常，请稍后再试');
            // 发起提现
            $pay_config = ChannelServer::config($channel, $this->shopid);
            $PayService = PayServer::init($pay_config['appid'], $pay_channel, $this->shopid);
            // 提现接口
            $withdraw_api = config('extend.WX_PAY_WITHDRAW_API');
            if($withdraw_api == 'v2'){
                $result = $PayService->server->toBalance([
                    'check_name' => 'NO_CHECK',
                    'partner_trade_no'  =>  $data['order_no'],
                    'openid'    =>  $openid,
                    'amount'    =>  $data['real_price'],
                    'desc'      =>  '提现'
                ]);
            }

            if($withdraw_api == 'v3'){
                $result = $PayService->server->toBalanceV3([
                    'appid'                 => $pay_config['appid'],
                    'out_batch_no'          => $data['order_no'], //商户系统内部的商家批次单号，要求此参数只能由数字、大小写字母组成，在商户系统内部唯一,
                    'batch_name'            => '用户提现',       //该笔批量转账的名称
                    'batch_remark'          => 'uid:' . $data['uid'] . "-" . '提现', //转账说明，UTF8编码，最多允许32个字符
                    'total_amount'          => $data['price'], //转账总金额 单位为“分”
                    'total_num'             => 1,
                    'transfer_detail_list'  => [
                        [
                            'out_detail_no'     => $data['order_no'],
                            'transfer_amount'   => $data['price'],
                            'transfer_remark'   => 'uid:' . $data['uid'] . "-" . '提现',
                            'openid'            => $openid,
                            //'user_name'         => $encryptor($params['name']) // 金额超过`2000`才填写
                        ]
                    ]
                ]);
                
                if(is_array($result) && isset($result['errCode']) && $result['errCode'] == 0) throw new Exception($result['errMsg']);
            }
            // 成功返回数据结构案例
            // 200 {"batch_id":"131000405062801234218022023061015509692778","create_time":"2023-06-10T13:22:31+08:00","out_batch_no":"202306100353637091"}
            // 记录日志
            Log::write($result, 'notice');


            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                //扣除冻结余额
                (new MemberWallet())->minusFreeze($data['shopid'], $data['uid'], $data['price']);

                //写入资金流水表
                $result_capital_flow = (new CapitalFlow())->createFlow([
                    'uid' => $data['uid'],
                    'order_no' => $data['order_no'],
                    'price' => $data['price'],
                    'shopid' => $data['shopid'],
                    'app' => 'system',
                    'channel' => $data['channel'],
                    'remark' => '用户提现',
                ]);
                if (!$result_capital_flow)  throw new Exception('写入资金流失失败');

                //更改提现记录状态
                $submit_data = [
                    'id'        => $withdraw_id,
                    'paid'      => 1,
                    'paid_time' => time(),
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            } else {
                //解冻冻结资金(返还至用户余额)
                $WalletModel->freeze($data['shopid'], $data['uid'], $data['price'], 0);
                //付款到零钱失败
                $submit_data = [
                    'id'     => $withdraw_id,
                    'error'  => 1,
                    'error_msg'  => json_encode($result)
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            }
            if (!$cash_with) {
                throw new Exception('网络异常,请稍后再试');
            }
            Db::commit();
            return $this->success('提现已提交，正在处理...');
        } catch (Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
    }
}
