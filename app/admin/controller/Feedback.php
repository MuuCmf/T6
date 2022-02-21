<?php
namespace app\admin\controller;

use think\facade\View;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\common\model\Feedback as FeedbackModel;
use app\common\logic\Feedback as FeedbackLogic;

/**
 * 用户反馈控制器
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
        $this->FeedbackLogic = new FeedbackLogic();
    }

    /**
     * 反馈列表
     */
    public function list()
    {
        $rows = 10;
        $map = [
            ['status', '>' , -1]
        ];
        // 获取列表
        $lists = $this->FeedbackModel->getListByPage($map, 'id DESC', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->FeedbackLogic->formatData($val);
        }
        unset($val);

        View::assign('pager',$pager);
        View::assign('lists', $lists);

        $this->setTitle('用户反馈');
        // 显示页面
        return View::fetch();
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
        if($status == 0){
            $title = '设置未处理';
        }
        if($status == 1){
            $title = '设置已处理';
        }
        if($status == -1){
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->FeedbackModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

}