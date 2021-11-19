<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Feedback as FeedbackModel;
use app\common\logic\Feedback as FeedbackLogic;
use app\common\model\Module as ModuleModel;

/**
 * 后台用户控制器
 */
class Feedback extends Admin
{
    protected $FeedbackModel;
    protected $FeedbackLogic;
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->FeedbackModel = new FeedbackModel();
        $this->ModuleModel = new ModuleModel();
        $this->FeedbackLogic = new FeedbackLogic();
    }

    /**
     * 用户反馈列表
     */
    public function list()
    {
        $keyword = input('keyword', '', 'text');
        View::assign('keyword',$keyword);
        // 每页显示数量
        $r = input('r', 20, 'intval');
        //初始化查询条件
        $map = [
            ['shopid', '=', 0],
        ];
        $status = input('get.status','all');
        if ($status != 'all'){
            $map[] = ['status', '=', $status];
        }else{
            $map[] = ['status', 'between', [0,99]];
        }
        $list = $this->FeedbackModel->getListByPage($map, 'id desc,create_time desc', '*', $r);
        $pager = $list->render();
        $list = $list->toArray();

        foreach($list['data'] as &$v){
            $v = $this->FeedbackLogic->_formatData($v);
        }
        unset($v);

        View::assign('pager',$pager);
        View::assign('lists',$list);
        View::assign([
            'pager' => $pager,
            'lists' => $list,
            'status' => $status
        ]);
        return View::fetch();
    }

    public function status(){
        $id = input('id', 0, 'intval');
        $status = input('status',0);

        $res = $this->FeedbackModel->edit([
            'id' => $id,
            'status' => $status
        ]);

        if($res){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }


}