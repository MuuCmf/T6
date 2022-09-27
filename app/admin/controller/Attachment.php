<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Attachment as AttachmentModel;
use think\exception\ValidateException;

/**
 * 附件管理控制器
 */
class Attachment extends Admin
{
    protected $AttachmentModel;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->AttachmentModel = new AttachmentModel();
    }

    /**
     * 附件列表
     */
    public function lists()
    {
        // 关键字
        $keyword = input('keyword','','text');
        View::assign('keyword',$keyword);
        // 驱动
        $driver = input('driver','','text');
        View::assign('driver',$driver);
        // 类型
        $type = input('type','','text');
        View::assign('type',$type);
        $rows = input('rows',20, 'intval');
        // 查询条件
        $map = [
            ['shopid', '=', 0]
        ];
        if(!empty($keyword)){
            $map[] = ['filename', 'like', '%'.$keyword.'%'];
        }
        if(!empty($driver)){
            $map[] = ['driver', '=', $driver];
        }
        if(!empty($type)){
            $map[] = ['type', '=', $type];
        }
        // 排序
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->AttachmentModel->getListByPage($map, $order, $fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        if(request()->isAjax()){
            // ajax请求返回数据
            return $this->success('success', $lists);
        }
        View::assign('pager',$pager);
        View::assign('lists',$lists);

        // 设置页面Title
        $this->setTitle('附件列表');
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        //输出页面
        return View::fetch();
    }

    /**
     * 附件数据写入和编辑附件表接口
     */
    public function edit()
    {
        $data = input('post.');

        $res = $this->AttachmentModel->edit($data);
        if($res){
            return $this->success('success');
        }else{
            return $this->error('error');
        }
    }

    /**
     * 删除附件数据风险较大，需谨慎操作
     */
    public function del()
    {

    }
}