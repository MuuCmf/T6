<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Author as AuthorModel;
use app\common\logic\Author as AuthorLogic;

class Author extends Admin
{
    protected $AuthorModel;
    protected $AuthorLogic;

    public function __construct()
    {
        parent::__construct();
        $this->AuthorModel = new AuthorModel();
        $this->AuthorLogic = new AuthorLogic();
    }

    /**
     * 作者列表
     */
    public function lists()
    {
        $map = [];
        $keyword = input('keyword','','text');
        View::assign('keyword', $keyword);
        if(!empty($keyword)){
            $map[] = ['name', 'like', '%'.$keyword.'%'];
        }
        $status = input('status', 'all');
        if($status === 'all'){
            $map[] = ['status', '>', -3];
        }
        if(intval($status) == 1){
            $map[] = ['status', '=', 1];
        }
        if(intval($status) == 0 && $status != 'all'){
            $map[] = ['status', '=', 0];
        }
        if(intval($status) == -1){
            $map[] = ['status', '=', -1];
        }
        if(intval($status) == -2){
            $map[] = ['status', '=', -2];
        }
        if(intval($status) == -3){
            $map[] = ['status', '=', -3];
        }
        View::assign('status', $status);
        $rows = input('rows',20, 'intval');
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = $order_field . ' ' . $order_type;

        // 获取分页列表
        $lists = $this->AuthorModel->getListByPage($map, $order, '*', $rows);
        // 分页按钮
        $pager = $lists->render();
        View::assign('pager',$pager);

        // 格式化数据
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->AuthorLogic->formatData($val);
        }
        unset($val);

        // ajax返回
        if(request()->isAjax()){
            return $this->success('success', $lists);
        }

        
        View::assign('lists',$lists);
        // 输出模板
        return View::fetch();
    }

    /**
     * 编辑/添加
     */
    public function edit()
    {   
        $id = input('id', 0, 'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title', $title);

        if (request()->isPost()) {
            $data = input();
            $data['shopid'] = $this->shopid;
            $res = $this->AuthorModel->edit($data);
            if($res){
                return $this->success($title . '成功',$res, url('lists'));
            }else{
                return $this->success($title . '失败');
            }
        }else{
            // 初始化数据结构
            $data = [];
            $data['id'] = 0;
            $data['uid'] = 0;
            $data['name'] = '';
            $data['description'] = '';
            $data['cover'] = '';
            $data['avatar_card'] = '';
            $data['certificate'] = '';
            $data['content'] = '';
            $data['sort'] = 0;
            $data['status'] = 0;
            $data['reason'] = '';
            $data['user_info'] = [];
            if(!empty($id)){
                $data = $this->AuthorModel->getDataById($id);
                $data = $this->AuthorLogic->formatData($data);
            }
            View::assign('data',$data);
            // 设置页面Title
            $this->setTitle($title . '老师');
            // 输出模板
            return View::fetch();
        }
    }

    /**
     * 设置内容状态
     */
    public function status()
    {   
        $ids = input('ids/a');
        !is_array($ids)&&$ids=explode(',',$ids);
        $status = input('status', 0, 'intval');
        $title = '更新';
        if($status == 0){
            $title = '禁用';
        }
        if($status == 1){
            $title = '启用';
        }
        if($status == -1){
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->AuthorModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

    /**
     * 检查用户老师绑定状态，禁止用户绑定多个老师
     */
    public function checkBind()
    {
        $id = intval(input('id'));
        $uid = intval(input('uid'));

        $res = $this->AuthorModel->getDataByMap([
            ['shopid', '=', 0],
            ['uid', '=', $uid]
        ]);
        if($res && $res['id'] != $id){
            return $this->error('该用户已绑定讲师数据');
        }else{
            return $this->success('验证成功，允许绑定讲师');
        }
    }

    /**
     * 状态审核
     */
    public function verify()
    {
        $id = input('id',0,'intval');
        View::assign('id', $id);
        if (request()->isPost()) {
            $status = input('status', -1, 'intval');
            $reason = input('reason', '', 'text');
            $res = $this->AuthorModel->where('id', $id)->update([
                'status' => $status,
                'reason' => $reason
            ]);

            if($res){
                return $this->success('操作成功');
            }else{
                return $this->error('操作失败');
            }  
        }

        if(!empty($id)){
            $data = $this->AuthorModel->getDataById($id);
            $data = $this->AuthorLogic->formatData($data);
        }
        View::assign('data',$data);

        // 输出模板
        return View::fetch();
    }

}