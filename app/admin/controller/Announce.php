<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Module as ModuleModel;
use app\common\model\Announce as AnnounceModel;
use app\common\logic\Announce as AnnounceLogic;

use app\admin\validate\Common;
use think\exception\ValidateException;
/**
 * 公告控制器
 */
class Announce extends Admin
{
    protected $ModuleModel;
    protected $AnnounceModel;
    protected $AnnounceLogic;

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
        $this->AnnounceLogic = new AnnounceLogic();
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
            ['status', '>', -1],
            ['shopid', '=', 0]
        ];
        // 搜索关键字
        $keyword = input('keyword', '', 'text');
        View::assign('keyword',$keyword);
        if(!empty($keyword)){
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $fields = '*';
        $rows = input('rows', 20, 'intval');
        $lists = $this->AnnounceModel->getListByPage($map, 'sort desc,create_time desc', $fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->AnnounceLogic->formatData($val);
        }
        unset($val);
        
        if(request()->isAjax()){
            // ajax请求返回数据
            return $this->success('success', $lists);
        }
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
            $data['shopid'] = $this->shopid;
            $data['uid'] = get_uid();
            // 数据验证
            try {
                validate(Common::class)->scene('announce')->check([
                    'title'  => $data['title'],
                    'content'  => $data['content'],
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }
            // 处理连接至数据
            if(!empty($data['link_type']) || !empty($data['link_title'])){
                $link_to = [
                    'type' => $data['link_type'],
                    'title' => $data['link_title'],
                    'type_title' => $data['link_type_title'],
                    'param' => $data['link_param']
                ];
                $data['link_to'] = json_encode($link_to);
            }
            
            // 写入数据表
            $res = $this->AnnounceModel->edit($data);
            
            if ($res) {
                return $this->success($title.'成功', $res, Cookie('__forward__'));
            } else {
                return $this->error($title.'失败');
            }

        }else{
            if(!empty($id)){
                $data = $this->AnnounceModel->getDataById($id);
                $data = $this->AnnounceLogic->formatData($data);
            }else{
                // 初始化数据
                $data = [];
                $data['id'] = 0;
                $data['type'] = 1;
                $data['title'] = '';
                $data['content'] = '';
                $data['cover'] = '';
                $data['status'] = 1;
                $data['sort'] = 0;
            }
            View::assign('data', $data);

            // 获取Micro应用是否安装
            $micro_is_setup = $this->ModuleModel->checkInstalled('micro');
            if($micro_is_setup == true){
                $micro_is_setup = 1;
            }else{
                $micro_is_setup = 0;
            }
            View::assign('micro_is_setup', $micro_is_setup);

            if($micro_is_setup){
                // 链接至参数
                bind('micro\\LinkSsevice', 'app\\micro\\service\\Link');
                $links = app('micro\\LinkSsevice')->getAllLinks();
                View::assign('links', $links);

                $link_static_tmpl = app('micro\\LinkSsevice')->getStaticTmpl();
                View::assign('link_static_tmpl', $link_static_tmpl);
            }

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
