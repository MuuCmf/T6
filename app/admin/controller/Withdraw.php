<?php
namespace app\admin\controller;

use app\common\model\CapitalFlow;
use app\common\model\MemberWallet;
use app\common\model\Withdraw as WithdrawModel;
use app\common\logic\Withdraw as WithdrawLogic;
use think\Exception;
use think\facade\Db;
use think\facade\View;

class Withdraw extends Admin{
    protected $WithdrawModel;
    protected $WithdrawLogic;
    function __construct()
    {
        parent::__construct();
        $this->WithdrawModel = new WithdrawModel();
        $this->WithdrawLogic = new WithdrawLogic();
    }

    /**
     * @title 提现列表
     * @return \think\response\View
     */
    public function lists(){
        $order_no = input('get.order_no','','string');//提现单号
        $map = [
            ['shopid' ,'=' ,$this->shopid]
        ];

        //订单号查询
        if (!empty($order_no)){
            $map[] = ['order_no' ,'like' ,"%{$order_no}%"];
        }
        // 每页显示数量
        $rows = input('rows', 15, 'intval');
        // 获取分页列表
        $lists = $this->WithdrawModel->getListByPage($map, 'id desc create_time desc', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        foreach ($lists['data'] as &$item){
            $item = $this->WithdrawLogic->formatData($item);
        }

        View::assign([
            'order_no'  =>  $order_no,
            'lists'     =>  $lists,
            'pager'     =>  $pager,
        ]);

        $this->setTitle('提现列表');

        return View::fetch();
    }

    /**
     * 详情
     */
    public function detail()
    {
        // ID
        $id = input('id', 0, 'intval');

        $data = [];
        if (!empty($id)) {
            $data = $this->WithdrawModel->getDataById($id);
            $data = $this->WithdrawLogic->formatData($data);
        }

        View::assign('data',$data);

        //输出页面
        return View::fetch();


    }

    /**
     * @title 手动处理
     */
    public function dealWith(){
        if (request()->isPost()){
            $id = input('post.id',0);
            try {
                $map = [
                    ['id' ,'=' ,$id],
                    ['error' ,'=' ,1],
                    ['paid' ,'=' ,0]
                ];
                $data = $this->WithdrawModel->where($map)->find()->toArray();
                if (!$data) throw new Exception('数据不存在');
                Db::startTrans();//开启事务
                //扣除用户余额及冻结余额
                (new MemberWallet())->spending($data['uid'],$data['price'],$data['shopid']);

                //更改提现记录状态
                $update_data = [
                    'id'        => $data['id'],
                    'paid'      => 1,
                    'paid_time' => time(),
                    'error'     =>  0,
                ];
                $result = $this->WithdrawModel->edit($update_data);
                if (!$result)   throw new Exception('网络异常,请稍后再试');

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
                Db::commit();
                $this->success('处理成功');
            }catch (Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
    }
}