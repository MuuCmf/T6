<?php
namespace app\admin\controller;

use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminConfigBuilder;
use think\facade\View;
use app\common\model\MessageType as MessageTypeModel;
use app\common\model\Message as MessageModel;
use think\exception\ValidateException;

/**
 * 消息控制器
 */
class Message extends Admin
{
    protected $MessageModel;
    protected $MessageTypeModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
        $this->MessageModel = new MessageModel();
        $this->MessageTypeModel = new MessageTypeModel();
    }

    /**
     * 消息类型
     */
    public function type()
    {
        $map = [];
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
    public function edit()
    {
        $id = input('id', 0, 'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title',$title);

        if (request()->isPost()) {
            $data = input();
            // 数据验证

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
        
    }


}