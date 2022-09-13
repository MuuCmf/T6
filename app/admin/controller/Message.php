<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\admin\model\AuthGroup;
use app\common\model\MessageContent as MessageContentModel;
use app\common\model\MessageType as MessageTypeModel;
use app\common\model\Message as MessageModel;

use app\admin\validate\Common;
use think\exception\ValidateException;

/**
 * 消息控制器
 */
class Message extends Admin
{
    protected $MessageModel;
    protected $MessageContentModel;
    protected $MessageTypeModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
        // 消息发送列表
        $this->MessageModel = new MessageModel();
        // 消息内容
        $this->MessageContentModel = new MessageContentModel();
        // 消息类型
        $this->MessageTypeModel = new MessageTypeModel();
        // 设置页面title
        $this->setTitle('消息管理');
    }

    /**
     * 消息类型
     */
    public function type()
    {
        // 查询条件
        $map[] = ['shopid', '=', 0];
        $map[] = ['status', '>', -1];
        $list = $this->MessageTypeModel->getList($map);
        foreach($list as &$val){
            $val = $this->MessageTypeModel->formatData($val);
        }
        unset($val);

        View::assign('list', $list);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 输出模板
        return View::fetch();
    }

    /**
     * 类型编辑、新增
     */
    public function typeEdit()
    {
        $id = input('id', 0, 'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title',$title);

        if (request()->isPost()) {
            $data = input();
            // 数据验证
            try {
                validate(Common::class)->scene('message_type')->check([
                    'title'  => $data['title'],
                    'description'  => $data['description'],
                    'icon'  => $data['icon'],
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }
            $res = $this->MessageTypeModel->edit($data);
            
            if ($res) {
                $this->success($title.'成功', $res, Cookie('__forward__'));
            } else {
                $this->error($title.'失败');
            }

        }else{
            if(!empty($id)){
                $data = $this->MessageTypeModel->getDataById($id);
            }else{
                // 初始化数据
                $data = [];
                $data['id'] = 0;
                $data['title'] = '';
                $data['description'] = '';
                $data['icon'] = '';
                $data['status'] = 1;
            }
            
            View::assign('data', $data);
            // 输出模板
            return View::fetch();
        }
    }

    /**
     * 手动消息发送
     */
    public function send()
    {
        if (request()->isPost()) {
            $data = input();
            // 数据验证
            try {
                validate(Common::class)->scene('message')->check($data);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }
            // 处理发送的类型
            $send_type = $data['send_type'];
            // 处理接收用户
            if(!empty($data['to_uid'])){
                $to_uids = intval($data['to_uid']);
                // 发送消息
                $res = $this->MessageModel->sendMessageToUid(0, 0, $to_uids, $data['title'], $data['description'], $data['content'], $data['type_id'], $send_type);

            }else{
                // 发送至用户组
                $to_group_ids = $data['user_group'];
                // 发送消息
                $res = $this->MessageModel->sendMessageToGroup(0, 0, $to_group_ids, $data['title'], $data['description'], $data['content'], $data['type_id'], $send_type);
            }
            
            if ($res) {
                return $this->success('消息发送成功', $res);
            } else {
                return $this->error('消息发送失败');
            }

        }else{
            
            // 消息类型ID
            $type_id = input('type_id', 0, 'intval');
            View::assign('type_id', $type_id);
            // 发送至用户
            $to_uid = input('to_uid', 0,'intval');
            View::assign('to_uid', $to_uid);
            if(!empty($to_uid)){
                $to_user = query_user($to_uid);
                View::assign('to_user', $to_user);
            }
            // 获取用户组数据
            if (empty($to_uid)) {
                $group = (new AuthGroup)->getGroups();
                $groups = array();
                foreach ($group as $v) {
                    array_push($groups, array('id' => $v['id'], 'value' => $v['title']));
                }
                View::assign('groups', $groups);
            }
            
            // 获取消息类型
            $map[] = ['shopid', '=', 0];
            $map[] = ['status', '=', 1];
            $type = $this->MessageTypeModel->getList($map);
            foreach($type as &$val){
                $val = $this->MessageTypeModel->formatData($val);
            }
            unset($val);
            View::assign('type', $type);
            

            // 输出模板
            return View::fetch();
        }
    }

    /**
     * 消息发送列表
     */
    public function list()
    {
        // 查询条件
        $map[] = ['shopid', '=', 0];
        $map[] = ['status', '>', -1];

        // 搜索关键字
        $keyword = input('keyword', '', 'text');
        View::assign('keyword',$keyword);
        if(!empty($keyword)){
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $fields = '*';
        $rows = input('rows', 20, 'intval');
        $lists = $this->MessageModel->getListByPage($map, 'id desc,create_time desc', $fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();

        foreach($lists['data'] as &$val){
            $val = $this->MessageModel->formatData($val);
        }
        unset($val);

        // ajax请求返回
        if (request()->isAjax()){
            return $this->success('success',$lists);
        }

        View::assign('pager',$pager);
        View::assign('lists',$lists);

        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->setTitle('消息发送记录');
        // 输出模板
        return View::fetch('list');
    }

    /**
     * 消息状态管理
     */
    public function messageStatus()
    {
        $ids = input('ids/a');
        !is_array($ids) && $ids = explode(',',$ids);
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

        $res = $this->MessageModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }


    /**
     * 消息类型状态管理
     */
    public function typeStatus()
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

        $res = $this->MessageTypeModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

    /**
     * 消息内容列表
     */
    public function content()
    {
        // 查询条件
        $map[] = ['shopid', '=', 0];
        $map[] = ['status', '>', -1];

        // 搜索关键字
        $keyword = input('keyword', '', 'text');
        View::assign('keyword',$keyword);
        if(!empty($keyword)){
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $fields = '*';
        $rows = input('rows', 20, 'intval');
        $lists = $this->MessageContentModel->getListByPage($map, 'id desc,create_time desc', $fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->MessageContentModel->formatData($val);
        }
        unset($val);

        // ajax请求返回
        if (request()->isAjax()){
            return $this->success('success',$lists);
        }

        View::assign('pager',$pager);
        View::assign('lists',$lists);

        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        
        // 输出模板
        return View::fetch('content');
    }

    /**
     * 消息内容新增、编辑
     */
    public function contentEdit()
    {
        $id = input('id', 0, 'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title',$title);

        if (request()->isPost()) {
            $data = input();
            // 数据验证
            try {
                validate(Common::class)->scene('message')->check([
                    'title'  => $data['title'],
                    'description'  => $data['description'],
                    'content'  => $data['content'],
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }
            $res = $this->MessageContentModel->edit($data);
            
            if ($res) {
                $this->success($title.'成功', $res, Cookie('__forward__'));
            } else {
                $this->error($title.'失败');
            }

        }else{
            if(!empty($id)){
                $data = $this->MessageContentModel->getDataById($id);
            }else{
                // 初始化数据结构
                $data['id'] = 0;
                $data['title'] = '';
                $data['description'] = '';
                $data['content'] = '';
                $data['status'] = 1;
            }
            View::assign('data', $data);

            // 输出模板
            return View::fetch();
        }
    }

    /**
     * 消息内容状态管理
     */
    public function contentStatus()
    {
        $ids = input('ids/a');
        !is_array($ids) && $ids = explode(',',$ids);
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

        $res = $this->MessageContentModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }


}