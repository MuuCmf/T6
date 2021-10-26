<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Feedback as FeedbackModel;
use app\common\model\Module as ModuleModel;

/**
 * 后台用户控制器
 */
class Feedback extends Admin
{
    protected $FeedbackModel;

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
            ['status', '>=', 0]
        ];

        $list = $this->FeedbackModel->getListByPage($map, 'id desc,create_time desc', '*', $r);
        $pager = $list->render();
        $list = $list->toArray();
        
        foreach($list['data'] as &$v){
            $v = $this->FeedbackModel->formatData($v);
        }
        unset($v);

        View::assign('pager',$pager);
        View::assign('lists',$list);

        return View::fetch();
    }


}