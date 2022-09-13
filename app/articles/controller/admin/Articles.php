<?php
namespace app\articles\controller\admin;

use think\facade\View;
use app\articles\model\ArticlesCategory as CategoryModel;
use app\articles\logic\Category as CategoryLogic;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;
use app\articles\validate\Articles as ArticlesValidate;
use think\exception\ValidateException;

class Articles extends Admin
{
    protected $CategoryModel;
    protected $CategoryLogic;
    protected $ArticlesModel;
    protected $ArticlesLogic;

    public function __construct()
    {
        parent::__construct();
        $this->CategoryModel = new CategoryModel(); //分类模型
        $this->CategoryLogic = new CategoryLogic(); //分类逻辑
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
    }

    /**
     * 文章列表页
     */
    public function lists()
    {
        $keyword = input('keyword', '', 'text');
        View::assign('keyword', $keyword);
        $category_id = input('category_id', 0, 'intval');
        View::assign('category_id', $category_id);
        $status = input('status') == null?'all':input('status');
        View::assign('status', $status);
        $rows = input('rows', 20, 'intval');

        // 获取查询条件
        $map = $this->ArticlesLogic->getMap($this->shopid, $keyword, $category_id, $status);
        // 获取列表
        $lists = $this->ArticlesModel->getListByPage($map, 'sort DESC,id DESC', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->ArticlesLogic->formatData($val);
        }
        unset($val);

        // ajax请求返回数据
        if(request()->isAjax()){
            return $this->success('success', $lists);
        }
        View::assign('pager',$pager);
        View::assign('lists',$lists);

        // 获取分类树
        $category_tree = (new CategoryModel())->tree($this->shopid, 1);
        View::assign('category_tree', $category_tree);
        
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->setTitle('文章列表');
        // 输出模板
        return View::fetch();
    }

    /**
     * 新增、编辑文章
     */
    public function edit()
    {
        $id = input('id',0,'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title',$title);

        if (request()->isPost()) {
            $data = input();
            // 数据验证
            try {
                validate(ArticlesValidate::class)->check([
                    'title'  => $data['title'],
                    'description' => $data['description'],
                    'cover' => $data['cover'],
                    'category_id' => $data['category_id']
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                // dump($e->getError());exit;
                return $this->error($e->getError());
            }

            $res = $this->ArticlesModel->edit($data);

            if($res){
                return $this->success($title . '成功', $res, Cookie('__forward__'));
            }else{
                return $this->error($title . '失败');
            }
        }

        //获取数据
        $data['id'] = 0;
        $data['title'] = '';
        $data['description'] = '';
        $data['cover'] = '';
        $data['category_id'] = 0;
        $data['sort'] = 0; // 排序
        $data['content'] = '';
        $data['f_view'] = 0;
        $data['f_support'] = 0;
        $data['f_favorites'] = 0;
        $data['status'] = 0; // 状态
        $data['reason'] = ''; // 审核拒绝原因

        if(!empty($id)){
            $data = $this->ArticlesModel->getDataById($id);
            $data = $this->ArticlesLogic->formatData($data);
        }
        View::assign('data',$data);

        // 获取分类树
        $category_tree = $this->CategoryModel->tree($this->shopid, 0);
        View::assign('category_tree', $category_tree);

        $this->setTitle($title.'文章');
        // 输出模板
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
            $title = '禁用';
        }
        if($status == 1){
            $title = '启用';
        }
        if($status == -3){
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->ArticlesModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

    public function verify()
    {
        
    }
}