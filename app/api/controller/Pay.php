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
use app\common\model\Orders as OrdersModel;
use app\unions\model\MiniProgramConfig;
use app\unions\model\WechatConfig;
use think\Exception;
use think\facade\Db;
use think\Request;

class Pay extends Base {

    private $PayService;//支付服务
    private $OrderModel;//订单模型
    private $OrderLogic;//订单模型
    private $params;//参数
    protected $middleware = [
        'app\\common\\middleware\\CheckParam',
        'app\\common\\middleware\\CheckAuth' => ['only' => 'pay'],
    ];

    function __construct(Request $request)
    {
        parent::__construct();
        //中间件加载完成后执行
        $this->initParams();//参数赋值
        $this->initPayService();//初始化支付服务
        $this->initOrderLogic();
        $this->OrderModel = new OrdersModel();

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
            1 => 'WechatPayment',
            2 => 'WechatPayment',
            3 => 'AlipayPayment',
        ];
        $pay_type = $this->params['pay_type'];
        //获取实例化的服务
        $pay_namespace = "app\\unions\\service\\pay\\{$className[$pay_type]}";
        $config = $this->initUnionConfig();
        $this->PayService = new $pay_namespace($config['appid']);
    }


    /**
     * 初始化渠道配置信息
     * @return MiniProgramConfig|WechatConfig|array|\think\Model
     */
    protected function initUnionConfig()
    {

        switch ($this->params['pay_type']){
            //微信公众号
            case 1:
                $data = (new WechatConfig())->getWechatConfigByShopId($this->params['shopid']);
                if (empty($data)){
                    throw  new Exception('公众号配置文件不存在');
                }
                break;
            //微信小程序
            case 2:
                //获取配置信息
                $map = [
                    ['shopid' ,'=' , $this->params['shopid']],
                    ['name' ,'=' , $this->params['app']],
                    ['platform' ,'=' ,'wechat']
                ];
                $data = (new MiniProgramConfig())->where($map)->find();
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
                $order_no = input('post.order_no');
                $order_data = $this->OrderModel->getDataByOrderNo($order_no);
                if (!$order_data){
                    throw new Exception('订单不存在');
                }
                $order_data = $this->OrderLogic->_formatData($order_data);
                $order_data['openid'] = MemberSync::where([
                    ['uid' , '=', request()->uid],
                    ['type', '=', $this->params['pay_type']]
                ])->value('openid');
                $pay = $this->PayService->pay($order_data);
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
                //处理订单业务
                $order = $this->orderService->refund($this->params);
                if (!$order){
                    throw new Exception('订单不存在');
                }
                //生成订单流水
                $this->createCapitalFlow($order);
                //退款逻辑
                if ($this->params['refund_to'] == 0){
                    //退款至用户账户
                    $result = Member::updateAmount($order['uid'],'balance',$order['price']);
                }elseif($this->params['refund_to'] == 1){
                    //退款至付款账户
                    $result = $this->payService->refund($order);
                    if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                        $result = true;
                    }else{
                        throw new Exception($result['return_code']);
                    }
                }
                if (!$result){
                    throw new Exception('网络异常，请稍后再试');
                }
                Db::commit();
                $this->success('退款成功');
            }catch (\Exception $e){
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
        //判断订单是否已支付
        $result = $this->payService->notify($notify);
        if ($result){//true未支付
            $this->orderService->notify($notify['out_trade_no']);
            //消息通知
        }
        $this->success('回调成功');
    }

    function createCapitalFlow($data){
        //生成订单流水
        $flow_data = [
            'shopid'    => $this->shopid,
            'uid'       => $data['uid'],
            'flow_no'   => $data['refund_no'] ?? CapitalFlow::build_flow_no(),
            'order_no'  => $data['order_no'],
            'channel'   => ($this->params['refund_to'] ?? 0) > 0 ? $data['channel'] : 0,
            'type'      => 1,
            'price'     => $data['price'],
            'remark'    => $data['remark'] ?? '',
            'status'    => $data['status'] ?? 1
        ];
        $flow_res = (new CapitalFlow())->edit($flow_data);
        if (!$flow_res){
            throw new Exception('生成流水号失败');
        }
    }
}