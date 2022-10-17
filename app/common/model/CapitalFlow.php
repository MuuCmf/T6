<?php
namespace app\common\model;

class CapitalFlow extends Base
{
    protected $autoWriteTimestamp = true;
    
    public function getPriceAttr($value,$data){
        return sprintf("%.2f",$value/100);
    }
    /**
     * 生成流水号
     * @return [type] [description]
     */
    public function build_flow_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 14);
    }

    public function createFlow($data)
    {
        $flow_no = $this->build_flow_no();
        //生成订单流水
        $flow_data = [
            'shopid'    => $data['shopid'],
            'app'       => $data['app'],
            'uid'       => $data['uid'],
            'flow_no'   => $flow_no,
            'order_no'  => $data['order_no'],
            'channel'   => $data['channel'],
            'type'      => $data['type'] ?? 1,
            'price'     => $data['price'],
            'remark'    => $data['remark'] ?? '',
            'status'    => $data['status'] ?? 1
        ];
        $res = $this->edit($flow_data);
        if ($res){
            return $flow_no;
        }
        
        return false;
    }

    /**
     * 获取收支明细数量
     */
    public function chargesTotal($shopid = 0, $app = '', $uid = 0)
    {
        $map = [
            ['shopid', '=', $shopid],
            ['uid', '=', $uid]
        ];
        if(!empty($app)){
            $map[] = ['app', '=', $app];
        }
        $total = $this->where($map)->count();

        return $total;
    }

    /**
     * 数据处理
     */
    public function handle($data)
    {
        if(!empty($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
            $data['create_time_friendly_str'] = friendly_date($data['create_time']);
        }
        if(!empty($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
            $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        }
        return $data;
    }
}