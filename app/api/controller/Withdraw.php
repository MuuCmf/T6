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
use think\Exception;
use think\Request;

class Withdraw extends Base {

    private $PayService;//支付服务
    private $OrderModel;//订单模型
    private $OrderLogic;//订单模型
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

}