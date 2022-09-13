<?php
namespace app\articles\controller\admin;

use think\facade\View;
use app\articles\model\ArticlesComment as CommentModel;
use app\articles\logic\Comment as CommentLogic;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;
use app\articles\validate\Articles;
use think\exception\ValidateException;

class Comment extends Admin
{
    protected $CommentModel;
    protected $CommentLogic;
    protected $ArticlesModel;
    protected $ArticlesLogic;

    public function __construct()
    {
        parent::__construct();
        $this->CommentModel = new CommentModel();
        $this->CommentLogic = new CommentLogic();
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
    }

    /**
     * 文章评论列表页
     */
    public function lists()
    {
        $keyword = input('keyword', '', 'text');
        View::assign('keyword', $keyword);
        $article_id = input('article_id', 0, 'intval');
        View::assign('article_id', $article_id);
        $status = input('status') == null?'all':input('status');
        View::assign('status', $status);
        // 获取查询条件
        $map = $this->CommentLogic->getMap($this->shopid, $keyword, $article_id, $status);
        // 获取列表
        $lists = $this->CommentModel->getListByPage($map, 'id DESC', '*', 20);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->CommentLogic->formatData($val);
        }
        unset($val);

        View::assign('pager',$pager);
        View::assign('lists',$lists);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        // SEO
        $this->setTitle('评论列表');
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
                validate(Articles::class)->check([
                    'title'  => $data['title'],
                ]);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
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
            $data = $this->CommentModel->getDataById($id);
            $data = $this->CommentLogic->formatData($data);
        }
        View::assign('data',$data);
        // SEO
        $this->setTitle($title.'评论');
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
        if($status == -1){
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->CommentModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

    /**
     * 评论审核
     */
    public function verify()
    {
        $id = input('id',0,'intval');
        View::assign('id',$id);

        if (request()->isPost()) {
            $data = input();
            
            $res = $this->CommentModel->edit($data);

            if($res){
                return $this->success('操作成功', $res, Cookie('__forward__'));
            }else{
                return $this->error('操作失败');
            }
        }

        return View::fetch();
    }
}