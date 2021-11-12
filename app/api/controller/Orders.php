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
use \app\common\model\Orders as OrdersModel;
use think\Exception;
use think\facade\Db;
use think\Request;

class Orders extends Base {
    protected $middleware = [
        'app\\common\\middleware\\CheckParam',
        'app\\common\\middleware\\CheckAuth',
    ];
    private $OrderLogic;//应用业务
    private $OrderModel;//订单模型
    private $params;//参数
    function __construct(Request $request)
    {
        parent::__construct();
        $this->initParams();//参数赋值
        $this->initOrderLogic();//初始化订单业务
        $this->OrderModel = new OrdersModel();
    }

    /**
     * 初始化订单业务
     */
    protected function initOrderLogic(){
        $order_namespace = "app\\{$this->params['app']}\\logic\\Orders";
        $this->OrderLogic = new $order_namespace;
    }

    /**
     * 初始化请求参数
     */
    protected function initParams(){
        $this->params = request()->param();
    }

    /**
     * 下单
     * @param $order_data
     */
    function create(){
        if (request()->isAjax()){
            Db::startTrans();
            try {
                //具体业务 分发到相应程序订单类
                $order = $this->OrderLogic->createOrder($this->params);
                $res = $this->OrderModel->edit($order);
                if (!$res){
                    throw new Exception('创建订单失败，请稍后再试');
                }
                Db::commit();
                $order = $this->OrderModel->getDataById($res);
                return $this->success('创建订单成功',$order);
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage());
            }
        }
    }

}