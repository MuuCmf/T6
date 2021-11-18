<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: CapitalFlow.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/2
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\model;
class CapitalFlow extends BaseModel{
    protected $autoWriteTimestamp = true;
    public function getPriceAttr($value,$data){
        return sprintf("%.2f",$value/100);
    }
    /**
     * 生成流水号
     * @return [type] [description]
     */
    protected function build_flow_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 14);
    }

    public function createFlow($data){
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
}