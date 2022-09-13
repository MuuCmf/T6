<?php
namespace app\api\controller;

use app\channel\facade\channel\Channel as ChannelServer;
use app\channel\facade\channel\Pay as PayServer;
use app\common\controller\Api;
use app\common\model\CapitalFlow;
use app\common\model\MemberSync;
use app\common\model\MemberWallet;
use app\common\model\Orders as OrdersModel;
use app\channel\facade\wechat\OfficialAccount;
use app\channel\facade\wechat\MiniProgram;
use think\Exception;
use think\facade\Db;
use think\Request;

class Pay extends Api 
{
    private $OrderModel;//订单模型
    private $OrderLogic;//订单模型
    private $CapitalFlowModel;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['only' => 'pay,refund'],
    ];

    function __construct(Request $request)
    {
        parent::__construct();
        $this->OrderModel = new OrdersModel();
        $this->CapitalFlowModel = new CapitalFlow();
    }

    /**
     * 发起支付
     */
    public function pay()
    {
        if (request()->isPost()){
            try {
                $order_no = $this->params['order_no'];
                $order_data = $this->OrderModel->getDataByOrderNo($order_no);
                if (!$order_data){
                    throw new Exception('订单不存在');
                }
                $order_namespace = "app\\{$order_data['app']}\\logic\\Orders";
                $this->OrderLogic = new $order_namespace;
                $order_data = $this->OrderLogic->formatData($order_data);

                // 获取用户openid
                $openid = MemberSync::where([
                    ['shopid', '=', $this->shopid],
                    ['uid' , '=', request()->uid],
                    ['type', '=', $order_data['channel']]
                ])->value('openid');
                

                //初始化支付数据
                $title = $order_data['products']['title'];
                if (mb_strlen($title, 'utf8') > 20){
                    $title = mb_substr($title, 0, 20, 'utf8') . '...';
                }
                $pay_data['body'] = $title;
                $pay_data['out_trade_no'] = $order_data['order_no'];
                $pay_data['total_fee'] = intval($order_data['paid_fee'] * 100);
                $pay_data['openid'] = $openid;
                //支付回调
                if (isset($this->params['notify_url'])){
                    $pay_data['notify_url'] = $this->params['notify_url'];
                }else{
                    $notify_url = request()->domain() . "/api/pay/callback";
                    $notify_url .= "/channel/{$order_data['channel']}";
                    $notify_url .= "/pay_channel/{$this->params['pay_channel']}";
                    $notify_url .= "/shopid/{$order_data['shopid']}";
                    $notify_url .= "/app/{$order_data['app']}";
                    $pay_data['notify_url'] = $notify_url;
                }
                // 获取支付参数
                $config = ChannelServer::config($order_data['channel'] ,$this->shopid);
                $PayService = PayServer::init($config['appid'], $this->params['pay_channel']);
                $pay = $PayService->server->pay($pay_data);
                //更改支付渠道标识
                $channel_map = [
                    // 主键ID
                    'id' => $order_data['id'],
                    // 支付渠道
                    'pay_channel' => $this->params['pay_channel']
                ];
                $this->OrderModel->edit($channel_map);
                return $this->success('success',$pay);
            }catch (Exception $e){
                return $this->error($e->getMessage());
            }
        }
    }

    /**
     * 退款
     * (待完善)
     */
    public function refund()
    {
        if (request()->isAjax()){
            //开启事务
            Db::startTrans();
            try {
                $refund_to = $this->params['refund_to'] ?? 1;
                //处理订单业务
                $refund_info = $this->OrderLogic->refund($this->params);
                //更改订单状态
                $order_data = [
                    'id' => $refund_info['order_id'],
                    'refund' => $this->params['refund'],
                    'refund_to' => $refund_to,
                    'status' => 4,//更改已完成
                ];

                //4为退款流程，其他只更改订单状态
                if ($this->params['refund'] == 4){
                    //是否已有退款记录
                    $map = [
                        ['order_no', '=', $refund_info['order_no']],
                        ['shopid', '=', $this->params['shopid']],
                        ['app', '=', $this->params['app']],
                    ];
                    $has_refund = $this->CapitalFlowModel->getDataByMap($map);
                    if ($has_refund){
                        if (is_object($has_refund)) $has_refund = $has_refund->toArray();
                        $refund_info['refund_no'] = $has_refund['flow_no'];
                    }else{
                        //订单流水
                        $flow_no = $this->CapitalFlowModel->createFlow([
                            'uid' => $refund_info['uid'],
                            'order_no' => $refund_info['order_no'],
                            'price' => $refund_info['refund_fee'],
                            'shopid' => $this->params['shopid'],
                            'app' => $this->params['app'],
                            'channel' => $refund_to == 0 ? 'balance' : $refund_info['channel'],
                            'type' => 2,
                            'status' => 0
                        ]);
                        if ($flow_no){
                            $refund_info['refund_no'] = $flow_no;
                        }else{
                            throw new Exception('创建退款订单失败');
                        }
                    }
                    if (!$has_refund || $has_refund['status'] == 0){
                        //退款逻辑
                        if ($refund_to == 0){
                            //退款至用户账户
                            $result = (new MemberWallet())->income($refund_info['uid'],$refund_info['refund_fee'],$refund_info['shopid'],false);
                        }else{
                            //退款至付款账户
                            $config = ChannelServer::config($this->params['channel'] ,$this->shopid);
                            $PayService = PayServer::init($config['appid'], $this->params['pay_channel']);
                            $result = $PayService->server->refund($refund_info);
                        }
                        if (!$result){
                            throw new Exception('网络异常，请稍后再试');
                        }
                        //更改流水状态
                        $this->CapitalFlowModel->where('flow_no',$refund_info['refund_no'])->update([
                            'update_time' => time(),
                            'status' => 1
                        ]);
                    }

                    $order_data['refund_no'] =  $refund_info['refund_no'];
                }

                $this->OrderModel->edit($order_data);
                Db::commit();
                return $this->success('处理成功');
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage());
            }
        }
    }

    /**
     * 支付成功回调
     */
    public function callback()
    {
        $notify_xml = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($notify_xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $notify = json_decode($jsonxml, true);
        //实例化支付服务
        $config = ChannelServer::config($this->params['channel'] ,$this->shopid);
        $PayService = PayServer::init($config['appid'], $this->params['pay_channel']);
        //返回商户订单号
        $order_no = $PayService->server->notify($notify);
        //判断订单是否已支付
        if (!$order_no){
            $this->payXmlMsg('FAIL','通信失败，请稍后再通知我');
        }
        // 获取订单数据
        $order_info =$this->OrderModel->getDataByOrderNo($order_no);
        if (!$order_info){
            $this->payXmlMsg('FAIL','没有查询到订单');
        }
        if ($order_info['paid'] == 1){
            $this->payXmlMsg('SUCCESS', '订单支付完成');
        }
        //实例化订单逻辑
        if($order_info['order_info_type'] == 'vipcard'){
            $order_namespace = "app\\common\\service\\VipOrders";
        }else{
            $order_namespace = "app\\{$order_info['app']}\\service\\Orders";
        }
        $this->OrderService = new $order_namespace;
        $result = $this->OrderService->paySuccess($order_info);
        
        //订单流水
        $this->CapitalFlowModel->createFlow([
            'uid' => $order_info['uid'],
            'order_no' => $order_info['order_no'],
            'price' => $order_info['paid_fee'],
            'shopid' => $this->params['shopid'],
            'app' => $this->params['app'],
            'channel' => $this->params['channel'],
        ]);

        //消息通知
        $this->sendPaySuccessTmplmsg($order_info['channel'], $order_info);
    
        return $this->payXmlMsg();
    }

    /**
     * 返回xml信息
     * @param string $code
     * @param string $msg
     * @return string
     */
    protected function payXmlMsg($code = 'SUCCESS',$msg = ''){
        $data = [
            'return_code' => $code,
            'return_msg'  => $msg
        ];
        echo array_to_xml($data);exit();
    }

    /**
     * 发送支付成功模板消息
     * @param $tmplmsg_config
     * @param $order_info
     */
    protected function sendPaySuccessTmplmsg($channel, $order_info){
        // 格式化商品数据
        if(!empty($order_info['products'])){
            $order_info['products'] = json_decode($order_info['products'], true);
        }else{
            return false;
        }
        
        // 公众号消息
        if($channel  == 'weixin_h5'){
            // 获取配置
            $weixin_config = (new \app\channel\model\WechatConfig())->getWechatConfigByShopId($this->shopid);

            //消息模板是否设置
            if (empty($weixin_config['tmplmsg']['tmplmsg']['pay_success'])){
                return false;
            }
            $msg_list = [];
            if (in_array('manager', $weixin_config['tmplmsg']['to']) && !empty($weixin_config['tmplmsg']['manager_uid'])){
                $msg_item['openid'] = get_openid($this->shopid, $weixin_config['tmplmsg']['manager_uid']);
                $msg_item['user_info'] = query_user($order_info['uid']);
                $msg_item['first'] = '客户的订单已支付成功';
                $msg_item['remark'] = '客户的订单已支付成功，如有任何问题请联系平台客服！';
                $msg_list[] = $msg_item;
            }
            if (in_array('user', $weixin_config['tmplmsg']['to'])){
                $msg_item['openid'] = get_openid($this->shopid, $order_info['uid']);
                $msg_item['user_info'] = query_user($order_info['uid']);
                $msg_item['first'] = '尊敬的客户，您的订单已支付成功';
                $msg_item['remark'] = '感谢您的支持，如有任何问题请联系平台客服！';
                $msg_list[] = $msg_item;
            }

            foreach ($msg_list as $item){
                $msg = [
                    'touser' => $item['openid'],
                    'template_id' => $weixin_config['tmplmsg']['tmplmsg']['pay_success'],
                    'data' => [
                        'first' => $item['first'],
                        'keyword1' => [
                            'value' => $item['user_info']['nickname'],
                            'color' => '#ff510'
                        ],
                        'keyword2' => [
                            'value' => $order_info['order_no'],
                            'color' => '#ff510'
                        ],
                        'keyword3' => [
                            'value' => sprintf("%.2f",$order_info['paid_fee']/100). '元',
                            'color' => '#ff510'
                        ],
                        'keyword4' => [
                            'value' => $order_info['products']['title'] ?? '商品',
                            'color' => '#ff510'
                        ],
                        'remark' => $item['remark'],
                    ],
                ];
                $res = @OfficialAccount::sendTemplateMsg($msg);
                //成功返回数据结构
                //array: [
                //     "errcode" => 0
                //     "errmsg" => "ok"
                //     "msgid" => 2538082547780780032
                //]
                return $res;
            }
        }

        // 小程序消息
        if($channel == 'weixin_mp'){
            // 获取配置
            $weixin_mp_config = (new \app\channel\model\WechatMpConfig())->getWechatMpConfigByShopId($this->shopid);
            //消息模板是否设置
            if (empty($weixin_mp_config['tmplmsg']['tmplmsg']['pay_success'])){
                return false;
            }
            $order_info['metadata'] = json_decode($order_info['meatdata'], true);
            $form_id = $order_info['metadata']['formId'];
            $msg_list = [];
            if (in_array('manager', $weixin_mp_config['tmplmsg']['to']) && !empty($weixin_config['tmplmsg']['manager_uid'])){
                $msg_item['openid'] = get_openid($this->shopid, $weixin_mp_config['tmplmsg']['manager_uid'], $channel);
                $msg_item['user_info'] = query_user($order_info['uid']);
                $msg_list[] = $msg_item;
            }
            if (in_array('user', $weixin_mp_config['tmplmsg']['to'])){
                $msg_item['openid'] = get_openid($this->shopid, $order_info['uid'], $channel);
                $msg_item['user_info'] = query_user($order_info['uid']);
                $msg_list[] = $msg_item;
            }
            
            foreach ($msg_list as $item){
                $msg = [
                    'touser' => $item['openid'],
                    'template_id' => $weixin_mp_config['tmplmsg']['tmplmsg']['pay_success'],
                    'page' => 'micro/pages/index',
                    'form_id' => $form_id,
                    'data' => [
                        'thing1' => [
                            'value' => $order_info['products']['title'],
                        ],
                        'character_string3' => [
                            'value' => $order_info['order_no'],
                        ],
                        'amount4' => [
                            'value' => sprintf("%.2f",$order_info['paid_fee']/100). '元',
                        ],
                        'time7' => [
                            'value' => $order_info['create_time'],
                        ],
                    ],
                ];
                $res = @MiniProgram::sendTemplateMsg($msg);

                return $res;
            }
        }
    }
}