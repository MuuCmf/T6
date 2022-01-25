<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Module as ModuleModel;
use app\common\model\Announce as AnnounceModel;
/**
 * 公告控制器
 */
class Announce extends Admin
{
    protected $ModuleModel;
    protected $AnnounceModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
        $this->ModuleModel = new ModuleModel();
        $this->AnnounceModel = new AnnounceModel();
        // 设置页面title
        $this->setTitle('公告管理');
    }

    /**
     * 列表
     */
    public function list()
    {
        // 查询条件
        $map = [
            ['status', '>', -1]
        ];

        $fields = '*';
        $lists = $this->AnnounceModel->getListByPage($map, 'create_time desc', $fields, 20);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->AnnounceModel->formatData($val);
        }
        unset($val);

        View::assign('pager',$pager);
        View::assign('lists',$lists);
        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 输出模板
        return View::fetch();
    }

    /**
     * 编辑、新增
     */
    public function edit()
    {
        $id = input('id', 0, 'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title',$title);

        if (request()->isPost()) {
            $data = input();
            // 数据验证
            $res = $this->AnnounceModel->edit($data);
            
            if ($res) {
                $this->success($title.'成功', $res, Cookie('__forward__'));
            } else {
                $this->error($title.'失败');
            }

        }else{
            if(!empty($id)){
                $data = $this->AnnounceModel->getDataById($id);
            }else{
                // 初始化数据
                $data = [];
                $data['id'] = 0;
                $data['type'] = 1;
                $data['title'] = '';
                $data['content'] = '';
                $data['cover'] = '';
                $data['status'] = 1;
            }
            
            View::assign('data', $data);
            
            // 输出模板
            return View::fetch();
        }
    }

    /**
     * 状态管理
     */
    public function status()
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

        $res = $this->AnnounceModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

}
