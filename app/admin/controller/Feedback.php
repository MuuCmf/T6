<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Feedback as FeedbackModel;
use app\common\logic\Feedback as FeedbackLogic;

/**
 * 用户反馈控制器
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
    public function __construct(
        ?FeedbackModel $FeedbackModel = null,
        ?FeedbackLogic $FeedbackLogic = null
    )
    {
        parent::__construct();
        $this->FeedbackModel = $FeedbackModel ?? new FeedbackModel();
        $this->FeedbackLogic = $FeedbackLogic ?? new FeedbackLogic();
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

        // ajax请求返回数据
        if (request()->isAjax()) {
            return $this->success('success', $lists);
        }

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
        $ids = input('ids/a', []);
        if (!is_array($ids)) {
            $ids = explode(',', (string)$ids);
        }
        
        // 验证 IDs
        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) {
            return $this->error('请选择要操作的数据');
        }
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