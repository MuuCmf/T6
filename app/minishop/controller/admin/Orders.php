<?php
namespace app\classroom\controller\admin;

use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminTreeListBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\controller\Admin as MuuAdmin;

class Orders extends MuuAdmin
{
    protected $knowledgeModel;
    protected $knowledgeColumnModel;
    protected $knowledgeOrdersModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->knowledgeColumnModel = model('knowledge/KnowledgeColumn'); //知识专栏模型
        $this->knowledgeModel = model('knowledge/Knowledge'); //知识内容模型
        $this->knowledgeOrdersModel = model('knowledge/KnowledgeOrders'); //知识讲师模型
    }

    /**
     * 系统默认展示的首页
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function index()
    {
        //默认首页
        return $this->fetch();
    }

    /**
     * 订单列表
     */
    public function lists()
    {   
        //搜索类型
        $search = input('search','','text');
        $this->assign('search',$search);
        $type = input('type','','text');
        $this->assign('type',$type);
        $keyword = input('keyword','','text');
        $this->assign('keyword',$keyword);
        //包含搜索类型时
        if(!empty($search)){
            if($search == 'order_no'){
                //订单号
                $map['order_no'] = $keyword;
            }
            if($search == 'uid'){
                //用户ID
                $map['uid'] = $keyword;
            }
        }
        //订单类型
        if(!empty($type)){
            $map['type'] = $type;
        }

        //获取订单列表
        $map['status'] = ['neq',-1];
        //$map['shop_id'] = $this->shopId;
        $list = $this->knowledgeOrdersModel->getListByPage($map,'create_time desc','*',10);
        $page = $list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);

        //dump($list->toArray()['data']);
        //统计订单数据
        //累计收入
        $total_all_map = ['paid' => 1];
        //$total_all_map['shop_id'] = $this->shopId;
        $total_all_sum = $this->knowledgeOrdersModel->where($total_all_map)->sum('paid_fee');
        $total_all_sum = sprintf("%.2f",$total_all_sum/100);
        $this->assign('total_all_sum',$total_all_sum);

        //本月收入
        $total_month_map = [
            //'shop_id' => $this->shopId,
            'paid' => 1,
            'paid_time' => ['between',[$this->monthTime()[0],$this->monthTime()[1]]],
        ];
        $total_month_sum = $this->knowledgeOrdersModel->where($total_month_map)->sum('paid_fee');
        $total_month_sum = sprintf("%.2f",$total_month_sum/100);
        $this->assign('total_month_sum',$total_month_sum);

        //本周收入
        $total_week_map = [
            //'shop_id' => $this->shopId,
            'paid' => 1,
            'paid_time' => ['between',[$this->weekTime()[0],$this->weekTime()[1]]],
        ];
        $total_week_sum = $this->knowledgeOrdersModel->where($total_week_map)->sum('paid_fee');
        $total_week_sum = sprintf("%.2f",$total_week_sum/100);
        $this->assign('total_week_sum',$total_week_sum);

        //输入页面
        return $this->fetch();
    }

    /**
     * 用户购买记录
     */
    public function user()
    {   
        $uid = input('uid',0,'intval');
        //搜索类型
        $search = input('search','','text');
        $this->assign('search',$search);
        $type = input('type','','text');
        $this->assign('type',$type);
        $keyword = input('keyword','','text');
        $this->assign('keyword',$keyword);
        //包含搜索类型时
        if(!empty($search)){
            if($search == 'order_no'){
                //订单号
                $map['order_no'] = $keyword;
            }
            if($search == 'uid'){
                //用户ID
                $map['uid'] = $keyword;
            }
        }
        //订单类型
        if(!empty($type)){
            $map['type'] = $type;
        }

        //获取订单列表
        $map['uid'] = $uid;
        $map['status'] = ['neq',-1];
        //$map['shop_id'] = $this->shopId;
        $list = $this->knowledgeOrdersModel->getListByPage($map,'create_time desc','*',10);
        $page = $list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
        //dump($list->toArray()['data']);exit;
        //获取用户信息
        $user_info = query_user([
            'uid',
            'nickname',
            'sex',
            'birthday',
            'reg_ip',
            'last_login_ip',
            'last_login_time',
            'avatar32',
            'avatar128',
            'title',
            'signature',
        ],$uid);
        $this->assign('user_info',$user_info);

        //输入页面
        return $this->fetch();
    }

    /**
     * 订单详情
     */
    public function detail()
    {   
        $id = input('id',0,'intval');
        
        if($id != 0){
            $data = $this->knowledgeOrdersModel->getDataById($id);
        }else{
            $data = null;
        }

        //dump($data->toArray());exit;

        $this->assign('data',$data);
        return $this->fetch(); 
    }

    /**
     * 设置内容状态
     * method 参数 undercarriage 下架 grounding 上架 delete 删除
     */
    public function setStatus()
    {   
        $id = input('id', 0, 'intval');
        $data['id'] = $id;
        $method = input('method','','text');
        if($method == 'undercarriage'){//下架
            $data['status'] = 0;
            $title = '禁用';
        }
        if($method == 'grounding'){//上架
            $data['status'] = 1;
            $title = '启用';
        }
        if($method == 'delete'){//删除
            $data['status'] = -1;
            $title = '删除';
        }

        $res = $this->knowledgeTeacherModel->editData($data);
        if($res){
            $this->success($title . '成功');
        }else{
            $this->error($title . '失败');
        }  
    }

    /**
     * 获取本周时间戳
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    private function weekTime()
    {
        $year = date("Y");
        $month = date("m");
        $day = date('w');
        $nowMonthDay = date("t");

        $firstday = date('d') - $day;
        if(substr($firstday,0,1) == "-"){
            $firstMonth = $month - 1;
            $lastMonthDay = date("t",$firstMonth);
            $firstday = $lastMonthDay - substr($firstday,1);
            $first_time = strtotime($year."-".$firstMonth."-".$firstday);
        }else{
            $first_time = strtotime($year."-".$month."-".$firstday);
        }
        
        $lastday = date('d') + (7 - $day);
        if($lastday > $nowMonthDay){
            $lastday = $lastday - $nowMonthDay;
            $lastMonth = $month + 1;
            $last_time = strtotime($year."-".$lastMonth."-".$lastday);
        }else{
            $last_time = strtotime($year."-".$month."-".$lastday);
        }

        return [$first_time,$last_time];
    }

    /**
     * 获取本月时间戳
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    private function monthTime()
    {
        $year = date("Y");
        $month = date("m");
        $allday = date("t");
        $first_time = strtotime($year."-".$month."-1");
        $last_time = strtotime($year."-".$month."-".$allday);

        return [$first_time,$last_time];
    }

}