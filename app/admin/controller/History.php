<?php
namespace app\admin\controller;

use app\common\model\History as HistoryModel;
use app\common\logic\History as HistoryLogic;
use think\facade\View;

class History extends Admin{
    protected $HistoryModel;
    protected $HistoryLogic;

    public function __construct()
    {
        parent::__construct();
        $this->HistoryModel = new HistoryModel();
        $this->HistoryLogic = new HistoryLogic();
    }
    function list(){
        $app = input('get.app','all');
        $uid = input('get.uid','');
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['status' ,'=' ,1]
        ];
        if ($app != 'all')  $map[] = ['app' ,'=' ,$app];//标识
        if (!empty($uid))  $map[] = ['uid' ,'=' ,$uid];
        // 获取分页列表
        $lists = $this->HistoryModel->getListByPage($map, 'id desc create_time desc', '*', 20);
        // 分页按钮
        $pager = $lists->render();
        // 格式化数据
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->HistoryLogic->formatData($val);
        }
        //全部应用
        $module_map =[
            
        ];
        $all_module = (new \app\common\model\Module())->getAll($module_map);
        unset($val);
        View::assign([
            'lists' =>  $lists['data'],
            'pager' =>  $pager,
            'uid'   =>  $uid,
            'all_module' => $all_module,
            'app'   =>  $app
        ]);
        return view();
    }

    /**
     * 设置状态
     */
    public function status()
    {
        $ids = input('ids/a');
        !is_array($ids)&&$ids=explode(',',$ids);
        $status = input('status', 0, 'intval');
        $title = '更新';
        if($status == -1){
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->HistoryModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }
    }
}