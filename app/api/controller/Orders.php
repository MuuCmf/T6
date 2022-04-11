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
use app\common\logic\Orders as OrdersLogic;
use \app\common\model\Orders as OrdersModel;
use think\Exception;
use think\facade\Db;
use think\Request;

class Orders extends Base {
    protected $middleware = [
        'app\\common\\middleware\\CheckParam',
        'app\\common\\middleware\\CheckAuth',
    ];
    private $ModuleOrderLogic;//应用业务
    private $OrderLogic;//订单逻辑
    private $OrderModel;//订单模型
    private $params;//参数
    function __construct(Request $request)
    {
        parent::__construct();
        $this->initParams();//参数赋值
        if (isset($this->params['app']))    $this->initModuleOrderLogic();//初始化订单业务
        $this->OrderLogic = new OrdersLogic();
        $this->OrderModel = new OrdersModel();
    }

    /**
     * 初始化模块订单业务
     */
    protected function initModuleOrderLogic(){
        $order_namespace = "app\\{$this->params['app']}\\logic\\Orders";
        $this->ModuleOrderLogic = new $order_namespace;
    }

    /**
     * 初始化请求参数
     */
    protected function initParams(){
        $this->params = request()->param();
    }

    /**
     * @title 下单
     * @return \think\Response|void
     */
    public function create(){
        if (request()->isAjax()){
            Db::startTrans();
            try {
                //具体业务 分发到相应程序订单类
                $order = $this->ModuleOrderLogic->createOrder($this->params);
                $res = $this->OrderModel->edit($order);
                if (!$res){
                    throw new Exception('创建订单失败，请稍后再试');
                }
                Db::commit();
                $order = $this->OrderModel->getDataById($res);
                return $this->success('创建订单成功',$order);
            }catch (Exception $e){
                Db::rollback();
                return $this->error($e->getMessage().",line:{$e->getLine()},'file:{$e->getFile()}");
            }
        }
    }

    /**
     * @title 订单列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(){
        if (\request()->isAjax()){
            $uid = request()->uid;
            $map = [
                ['shopid','=',$this->params['shopid']],
                ['uid','=',$uid],
            ];

            if ($this->params['status']  == 'all'){
                $map[] = ['status' ,'between' ,[-1,9999]];
            }else{
                $map[] = ['status' ,'=' ,$this->params['status']];
            }

            $rows = $this->params['rows'] ?? 15;
            $list = $this->OrderModel->where($map)->page($this->params['page'],$rows)->order('id','DESC')->select()->toArray();
            foreach ($list as &$item){
                $item = $this->OrderLogic->formatData($item);
            }
            unset($item);
            $this->success('获取订单成功',$list);
        }
    }


}