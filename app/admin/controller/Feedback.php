<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Feedback as FeedbackModel;

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
    }


}