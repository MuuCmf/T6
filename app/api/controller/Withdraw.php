<?php
namespace app\api\controller;

use app\channel\facade\channel\Channel as ChannelServer;
use app\channel\facade\channel\Pay as PayServer;
use app\common\controller\Base;
use app\common\model\CapitalFlow;
use app\common\model\CapitalFlow as CapitalFlowModel;
use app\common\model\MemberWallet;
use app\common\model\Withdraw as WithdrawModel;
use app\common\logic\Withdraw as WithdrawLogic;
use think\Exception;
use think\facade\Db;
use think\Request;

class Withdraw extends Base {

    private $PayService;//支付服务
    private $WithdrawModel;//订单模型
    private $WithdrawLogic;//订单模型
    private $CapitalFlowModel;
    private $params;//参数
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    function __construct(Request $request)
    {
        parent::__construct();
        //中间件加载完成后执行
        $this->initParams();//参数赋值
        $this->initService();//初始化支付服务
        $this->WithdrawModel = new WithdrawModel();
        $this->WithdrawLogic = new WithdrawLogic();
        $this->CapitalFlowModel = new CapitalFlowModel();
    }


    /**
     * 初始化请求参数
     */
    protected function initParams(){
        $this->params = request()->param();
    }

    /**
     * 初始化支付
     */
    protected function initService(){
        $config = ChannelServer::config($this->params['channel'] ,$this->params['shopid']);
        $this->PayService = PayServer::init($config['appid'],$this->params['channel'],$this->params['shopid']);
    }

    /**
     * @title 提现
     */
    public function withdraw(){
        $uid = \request()->uid;
        Db::startTrans();
        try {
            $config = $this->WithdrawLogic->getConfig();//获取提现配置
            //是否开启提现
            if ($config['status'] < 1) throw new Exception('提现暂时关闭，如有特殊需求请联系客服');
            //初始化提现数据
            $data['shopid'] =   $this->params['shopid'];
            $data['uid']    =   $uid;
            $data['price']  =   $this->params['price'];
            $data['order_no']   =   build_order_no();//生成提现单号
            $data['channel']    =   $this->params['channel'];
            $data['error']  =   0;
            $data['paid']  =   0;
            //扣除平台手续费后，实际到账金额
            $rate = floatval($config['tax_rate']) / 1000;
            $deduct_money = ceil($data['price'] * $rate * 100) / 100;
            $data['real_price'] = $real_price = intval(ceil(($data['price'] - $deduct_money) *100));//单位分
            $data['price'] = intval($this->params['price'] * 100);//提现金额 单位分
            //最低金额
            if ($data['price'] < intval($config['min_price']) * 100) throw new Exception('提现金额最少为' . $config['min_price'] . '元');
            //最大金额
            if ($data['price'] > intval($config['max_price']) * 100) throw new Exception('提现金额最多为' . $config['max_price'] . '元');
            //查询今日提现次数
            $today_unixtime = dayTime();//今日时间戳
            $check_map = [
                ['shopid' ,'=' ,$this->params['shopid']],
                ['uid' ,'=' ,$uid],
                ['create_time' ,'between' ,[$today_unixtime[0],$today_unixtime[1]]]
            ];
            $withdraw_order_total = $this->WithdrawModel->where($check_map)->count();
            if ($withdraw_order_total >= $config['day_num']) throw new Exception('每日最多可提现' . $config['day_num'] . '次');
            //获取用户openid
            $openid = get_openid($uid ,$this->params['channel']);
            if (!$openid)   throw new Exception('用户不存在');
            //用户钱包模型
            $WalletModel = new MemberWallet();
            $wallet = $WalletModel->where('uid',$uid)->find()->toArray();
            if (!intval($wallet['balance'] - $wallet['freeze']) >= $data['price']) {
                throw new Exception('账户余额不足');
            }
            //冻结资金
            $WalletModel->freeze($uid,$data['price'],$this->params['shopid']);
            //提现记录
            $withdraw_id = $this->WithdrawModel->edit($data);
            if (!$withdraw_id)  throw new Exception('网络异常，请稍后再试');
            $result = $this->PayService->server->toBalance([
                'partner_trade_no'  =>  $data['order_no'],
                'openid'    =>  $openid,
                'amount'    =>  $data['price'],
                'desc'      =>  '提现'
            ]);
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                //扣除用户余额及冻结余额
                (new MemberWallet())->spending($data['uid'],$data['price'],$data['shopid']);

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
                    'id'        => $data['id'],
                    'paid'      => 1,
                    'paid_time' => time(),
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            }else{
                //付款到零钱失败
                $submit_data = [
                    'id'     => $withdraw_id,
                    'error'  => json_encode($result)
                ];
                $cash_with = $this->WithdrawModel->edit($submit_data);
            }
            if (!$cash_with)   throw new Exception('网络异常,请稍后再试');
            Db::commit();
            $this->success('提现正在处理，请耐心等待');
        }catch (\Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
    }

}