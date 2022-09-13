<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Evaluate as EvaluateModel;
use app\common\logic\Evaluate as EvaluateLogic;
use app\common\model\Orders as OrderModel;
use \app\common\logic\Orders as OrderLogic;

class Evaluate extends Api 
{
    protected $model;
    protected $logic;
    protected $OrderModel;
    protected $OrderLogic;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['except' => 'lists']
    ];
    function __construct()
    {
        parent::__construct();
        $this->logic = new EvaluateLogic();
        $this->model = new EvaluateModel();
        $this->OrderModel = new OrderModel();
        $this->OrderLogic = new OrderLogic();
    }

    public function lists()
    {
        $app = input('get.app');
        $type = input('get.type');
        $type_id = intval(input('get.type_id'));
        $map = [
            ['shopid','=',$this->shopid],
            ['status','=',1],
            ['app','=',$app],
            ['type','=',$type],
            ['type_id','=',$type_id]
        ];
        $rows = $params['rows'] ?? 10;
        $page = $params['page'] ?? 1;
        $lists = $this->model->where($map)->page($page,$rows)->order('id DESC')->select();
        foreach ($lists as &$item){
            $item = $this->logic->formatData($item);
        }
        unset($item);
        $count = $this->model->where($map)->count();
        return $this->success('SUCCESS',['data' => $lists,'total' => $count]);
    }

    /**
     * 题交和修改评价
     */
    public function edit(){
        $params = request()->param();
        $uid = request()->uid;
        $id = 0;
        if(empty($params['content'])){
            $this->error('评价内容不能为空');
        }
        //检测是否已评论
        $evaluate_map = [];
        $evaluate_map[] = ['uid','=',$uid];
        $evaluate_map[] = ['order_no','=',$params['order_no']];
        $evaluate_map[] = ['shopid','=',$this->shopid];
        $is_have = $this->model->getDataByMap($evaluate_map);
        if($is_have && $is_have['status'] == 1){
            if($is_have['create_time'] != $is_have['update_time']){
                $this->error('您已经评价过了');
            }
            $id = $is_have['id'];
        }
        //处理评价图片
        $images = '';
        if(!empty($params['images'])){
            $images = $params['images'];
            $images = explode(',', $images);
        }
        
        //提交
        $data = [
            'id' => $id,
            'shopid' => $this->shopid,
            'app' => get_module_name(),
            'uid' => $uid,
            'type' => $params['type'],
            'type_id' => intval($params['type_id']),
            'order_no' => $params['order_no'],
            'content' => html_entity_decode($params['content']),
            'images' => json_encode($images),
            'value' => $params['value'],
            'status' => 1
        ];
        $res = $this->model->edit($data);
        if ($res){
            //更改订单评价状态
            $order_info = $this->OrderModel->getDataByOrderNo($params['order_no']);
            $order_data = [
                'id' => $order_info['id'],
                'status' => 5, //已评价
            ];
            $this->OrderModel->edit($order_data);
            return $this->success('提交成功',$res);
        }
        return $this->error('提交失败，请稍后再试');
    }

    public function detail(){
        $uid = request()->uid;
        $order_no = input('get.order_no');
        //获取评价数据
        $map = [
            ['shopid','=',$this->shopid],
            ['order_no','=',$order_no],
            ['uid','=',$uid]
        ];
        $result = $this->model->getDataByMap($map);
        $result = $this->logic->formatData($result);
        return $this->success('SUCCESS',$result);
    }
}