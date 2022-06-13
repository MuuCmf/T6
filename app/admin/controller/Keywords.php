<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Keywords as KeywordsModel;
use app\common\logic\Keywords as KeywordsLogic;

use app\admin\validate\Common;
use think\exception\ValidateException;
/**
 * 搜索关键字控制器
 */
class Keywords extends Admin
{
    protected $KeywordsModel;
    protected $KeywordsLogic;
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
        $this->KeywordsModel = new KeywordsModel();
        $this->KeywordsLogic = new KeywordsLogic();
        // 设置页面title
        $this->setTitle('搜索关键字管理');
    }

    /**
     * 列表
     */
    public function lists()
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
        $lists = $this->KeywordsModel->getListByPage($map, 'create_time desc', $fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();

        foreach($lists['data'] as &$val){
            $val = $this->KeywordsLogic->formatData($val);
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
            // 数据验证
            try {
                validate(Common::class)->scene('keywords')->check([
                    'keyword'  => $data['keyword'],
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }
            
            // 写入数据表
            $res = $this->KeywordsModel->edit($data);
            
            if ($res) {
                $this->success($title.'成功', $res, Cookie('__forward__'));
            } else {
                $this->error($title.'失败');
            }

        }else{
            if(!empty($id)){
                $data = $this->KeywordsModel->getDataById($id);
                $data = $this->KeywordsLogic->formatData($data);
            }else{
                // 初始化数据
                $data = [];
                $data['id'] = 0;
                $data['keyword'] = '';
                $data['sort'] = 0;
                $data['recommend'] = 0;
                $data['status'] = 1;
            }
            View::assign('data', $data);
            // 设置页面title
            $this->setTitle($title . '搜索关键字');

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

        $res = $this->KeywordsModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

}
