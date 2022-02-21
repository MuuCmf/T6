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
use app\channel\facade\channel\Channel as ChannelServer;
use app\channel\facade\channel\Pay as PayServer;
use app\common\controller\Base;
use app\common\model\CapitalFlow as CapitalFlowModel;
use app\common\model\Withdraw as WithdrawModel;
use think\Exception;
use think\Request;

class Withdraw extends Base {

    private $PayService;//支付服务
    private $WithdrawModel;//订单模型
    private $WithdrawLogic;//订单模型
    private $CapitalFlowModel;
    private $params;//参数
    protected $middleware = [
//        'app\\common\\middleware\\CheckAuth',
    ];

    function __construct(Request $request)
    {
        parent::__construct();
        //中间件加载完成后执行
        $this->initParams();//参数赋值
        $this->initService();//初始化支付服务
        $this->WithdrawModel = new WithdrawModel();
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

    public function test(){
        dump($this->PayService);
        echo 123;
    }

}