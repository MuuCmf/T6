<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\logic\Orders as OrdersLogic;
use \app\common\model\Orders as OrdersModel;
use think\Exception;
use think\facade\Db;
use think\Request;

class Orders extends Api
{
    protected $middleware = [
        'app\\common\\middleware\\CheckParam',
        'app\\common\\middleware\\CheckAuth',
    ];

    private $OrdersModel;//订单模型
    private $OrdersLogic;//订单逻辑
    function __construct(Request $request)
    {
        parent::__construct();
        $this->OrdersLogic = new OrdersLogic();
        $this->OrdersModel = new OrdersModel();
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
                $this->params['uid'] = get_uid();
                $order_info_type = $this->params['order_info_type'];
                if($order_info_type == 'vipcard'){
                    $order_namespace = "app\\common\\service\\VipOrders";
                    $appOrdersService = new $order_namespace;
                    $order_data = $appOrdersService->create($this->params);
                }else{
                    $order_namespace = "app\\{$this->params['app']}\\service\\Orders";
                    $appOrdersService = new $order_namespace;
                    $order_data = $appOrdersService->create($this->params);
                }

                // 设置元数据
                if(isset($this->params['formId'])){
                    $metadata = [
                        'formId' => $this->params['formId']
                    ];
                    $order_data['metadata'] = json_encode($metadata);
                }
                
                //写入订单
                $res = $order_id = $this->OrdersModel->edit($order_data);
                if (!$res){
                    throw new Exception('创建订单失败，请稍后再试');
                }
                //获取订单数据
                $order = $this->OrdersModel->getDataById($order_id);
                $order = $this->OrdersLogic->formatData($order);
                //虚拟免费商品后续处理
                if($order['paid_fee'] == 0){
                    if(method_exists($appOrdersService,'step')){
                        // 免费商品直接处理后续逻辑
                        $appOrdersService->step($order);
                    }
                }
                Db::commit();

                return $this->success('创建订单成功',$order);
            }catch (Exception $e){
                Db::rollback();
                if (\think\facade\App::isDebug()){
                    return $this->error($e->getMessage().$e->getFile().$e->getLine());
                }else{
                    return $this->error($e->getMessage());
                }
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
        
        $uid = request()->uid;
        $status = input('status');
        $rows = input('rows', 15, 'intval');
        $map = [
            ['shopid','=',$this->shopid],
            ['uid','=',$uid],
        ];

        if ($this->params['status']  == 'all'){
            $map[] = ['status' ,'between' ,[0,9999]];
        }else{
            $map[] = ['status' ,'=' ,$status];
        }

        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order =  $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->OrdersModel->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->OrdersLogic->formatData($val);
        }
        unset($val);

        return $this->success('获取订单成功',$lists);
        
    }

    /**
     * @title 订单详情
     */
    public function detail(){
        $order_no = $this->params['order_no'];
        $order_data = $this->OrdersModel->getDataByOrderNo($order_no);
        $order_data = $this->OrdersLogic->formatData($order_data);
        return $this->success('success',$order_data);
    }


}