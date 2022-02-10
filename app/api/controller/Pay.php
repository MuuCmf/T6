<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Payment.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/2
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\controller\Base;
use app\common\model\CapitalFlow;
use app\common\model\Member;
use app\common\model\MemberSync;
use app\common\model\Orders;
use app\common\model\Orders as OrdersModel;
use app\unions\facade\wechat\OfficialAccount;
use app\unions\model\WechatMpConfig;
use app\unions\model\WechatConfig;
use think\Exception;
use think\facade\Db;
use think\facade\Log;
use think\Request;

class Pay extends Base {

    private $PayService;//支付服务
    private $OrderModel;//订单模型
    private $OrderLogic;//订单模型
    private $CapitalFlowModel;
    private $params;//参数
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['only' => 'pay'],
    ];

    function __construct(Request $request)
    {
        parent::__construct();
        //中间件加载完成后执行
        $this->initParams();//参数赋值
        if ($request->action() != 'payCallback'){
            $this->initPayService();//初始化支付服务
            $this->initOrderLogic();
        }
        $this->OrderModel = new OrdersModel();
        $this->CapitalFlowModel = new CapitalFlow();
    }


    /**
     * 初始化请求参数
     */
    protected function initParams(){
        $this->params = request()->param();
    }

    /**
     * 初始化订单业务
     */
    protected function initOrderLogic(){
        $order_namespace = "app\\{$this->params['app']}\\logic\\Orders";
        $this->OrderLogic = new $order_namespace;
    }



    /**
     * 初始化支付
     */
    protected function initPayService(){
        //服务类
        $className = [
            'weixin_h5' => 'WechatPayment',
            'weixin_app' => 'WechatPayment',
            'alipay' => 'AlipayPayment',
        ];
        //获取实例化的服务
        $pay_namespace = "app\\unions\\service\\pay\\{$className[$this->params['channel']]}";
        $config = $this->initUnionConfig();
        $this->PayService = new $pay_namespace($config['appid']);
    }


    /**
     * 初始化渠道配置信息
     * @return WechatMpConfig|WechatConfig|array|\think\Model
     */
    protected function initUnionConfig()
    {

        switch ($this->params['channel']){
            //微信公众号
            case 'weixin_h5':
                $data = (new WechatConfig())->getWechatConfigByShopId($this->params['shopid']);
                if (empty($data)){
                    throw  new Exception('公众号配置文件不存在');
                }
                break;
            //微信小程序
            case 'weixin_app':
                //获取配置信息
                $map = [
                    ['shopid' ,'=' , $this->params['shopid']],
                ];
                $data = (new WechatMpConfig())->where($map)->find();
                if (empty($data)){
                    throw  new Exception('小程序配置信息不存在');
                }
                break;
        }
        return $data;
    }

    public function pay(){
        if (request()->isPost()){
            try {
                $order_no = $this->params['order_no'];
                $order_data = $this->OrderModel->getDataByOrderNo($order_no);
                if (!$order_data){
                    throw new Exception('订单不存在');
                }
                $order_data = $this->OrderLogic->_formatData($order_data);
                $order_data['openid'] = MemberSync::where([
                    ['uid' , '=', request()->uid],
                    ['type', '=', $this->params['channel']]
                ])->value('openid');
                //初始化支付数据
                $pay_data['body'] = $order_data['products']['title'];
                $pay_data['out_trade_no'] = $order_data['order_no'];
                $pay_data['total_fee'] = $order_data['price'] * 100;
                $pay_data['openid'] = $order_data['openid'];
                //支付回调
                if (isset($this->params['notify_url'])){
                    $pay_data['notify_url'] = $this->params['notify_url'];
                }else{
                    $notify_url = request()->domain() . "/api/pay/payCallback";
                    $notify_url .= "/channel/{$this->params['channel']}";
                    $notify_url .= "/shopid/{$this->params['shopid']}";
                    $notify_url .= "/app/{$this->params['app']}";
                    $pay_data['notify_url'] = $notify_url;
                }
                $pay = $this->PayService->pay($pay_data);
                //更改支付渠道标识
                $channel_map = [
                    'id' => $order_data['id'],
                    'channel' => $this->params['channel']
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
     */
    function refund(){
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
                            $result = Member::updateAmount($refund_info['uid'],'balance',$refund_info['refund_fee']);
                        }else{
                            //退款至付款账户
                            $result = $this->PayService->refund($refund_info);
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
                $this->success('处理成功');
            }catch (Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
    }

    /**
     * 支付成功回调
     *
     */
    function payCallback(){
        $notify_xml = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($notify_xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $notify = json_decode($jsonxml, true);
        //实例化支付服务
        $this->initPayService();
        $order_no = $this->PayService->notify($notify);
        //判断订单是否已支付
        if (!$order_no){
            $this->payXmlMsg('FAIL','通信失败，请稍后再通知我');
        }

        $order_info =$this->OrderModel->getDataByOrderNo($order_no);
        if (!$order_info){
            $this->payXmlMsg('FAIL','没有查询到订单');
        }
        if ($order_info['paid'] == 1){
            $this->payXmlMsg('订单支付完成');
        }
        //实例化订单逻辑
        $this->initOrderLogic();
        //处理订单
        $result = $this->OrderLogic->paySuccess($order_info);
        //消息通知
        if (isset($result['tmplmsg']) && $result['tmplmsg']['switch'] == 1){
            $this->sendPaySuccessTmplmsg($result['tmplmsg'],$result['order_info']);
        }
        //订单流水
        $this->CapitalFlowModel->createFlow([
            'uid' => $order_info['uid'],
            'order_no' => $order_info['order_no'],
            'price' => $order_info['paid_fee'],
            'shopid' => $this->params['shopid'],
            'app' => $this->params['app'],
            'channel' => $this->params['channel'],
        ]);
        $this->payXmlMsg();
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
     * 发送支付成功公众号模板消息
     * @param $tmplmsg_config
     * @param $order_info
     */
    protected function sendPaySuccessTmplmsg($tmplmsg_config,$order_info){
        //消息模板是否设置
        if (empty($tmplmsg_config['pay_success'])){
            return false;
        }
        $msg_list = [];
        if (strstr($tmplmsg_config['to'],'manager')){
            $msg_item['openid'] = get_openid($tmplmsg_config['manager_uid']);
            $msg_item['user_info'] = query_user($order_info['uid']);
            $msg_item['first'] = '客户的订单已支付成功';
            $msg_item['remark'] = '客户的订单已支付成功，如有任何问题请联系平台客服！';
            $msg_list[] = $msg_item;
        }
        if (strstr($tmplmsg_config['to'],'user')){
            $msg_item['openid'] = get_openid($order_info['uid']);
            $msg_item['user_info'] = query_user($order_info['uid']);
            $msg_item['first'] = '尊敬的客户，您的订单已支付成功';
            $msg_item['remark'] = '感谢您的支持，如有任何问题请联系平台客服！';
            $msg_list[] = $msg_item;
        }

        foreach ($msg_list as $item){
            $msg = [
                'touser' => $item['openid'],
                'template_id' => $tmplmsg_config['pay_success'],
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
            @OfficialAccount::sendTemplateMsg($msg);
        }
    }
}