<?php
declare(strict_types = 1);
namespace app\minishop\crontab;

use app\common\model\CrontabLog;
use app\common\model\Orders as OrderModel;
use app\channel\service\bytedance\DouyinMp as DouyinMpService;
use app\channel\model\DouyinMpSettle as DouyinMpSettleModel;
use think\Exception;
use think\facade\Db;

/**
 * @title 自动结算抖音订单 计划任务
 */
class DouyinSettle
{
    protected $OrderModel;
    public function __construct()
    {
        $this->OrderModel = new OrderModel();
        $this->DouyinMpService = new DouyinMpService();
        $this->DouyinMpSettleModel = new DouyinMpSettleModel();
    }

    /**
     * @title 业务处理
     * @param int $shopid
     * @param int $task_id
     * @return bool
     */
    public function handle(int $shopid ,int $task_id){

        try {
            //查询需要结算的订单
            $map = [
                ['shopid', '=', $shopid],
                ['paid', '=', 1],
                ['paid_time', '<', time() - (7 * 24 * 60 * 60)],
            ];
            $lists = $this->OrderModel->where($map)->field('id')->select()->toArray();
            if (!empty($lists)){
                // 循环执行结算逻辑
                foreach($lists as $v){
                    $settle_no = build_order_no();
                    $order_no = $v['order_no'];
                    $result = $this->DouyinMpService->settle($settle_no, $order_no);
                    $result = json_decode($result, true);
                    if($result['err_no'] == 0){
                        $this->DouyinMpSettleModel->edit([
                            'settle_no' => $settle_no,
                            'order_no' => $order_no,
                            'price' => $v['paid_fee'],
                            'douyin_settle_no' => $result['settle_no'],
                            'status' => 0
                        ]);
                    }
                }
            }

            CrontabLog::addLog([
                'shopid' => $shopid,
                'cid'    => $task_id,
                'description'   =>  'success'
            ]);

            
            return true;
        }catch (Exception $e){
            Db::rollback();
            CrontabLog::addLog([
                'shopid' => $shopid,
                'cid'    => $task_id,
                'description'   =>  $e->getMessage(),
                'status'        =>  0
            ]);
            return false;
        }

    }
}