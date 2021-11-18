<?php
namespace app\articles\controller\admin;

use think\facade\View;
use app\articles\model\ArticlesCategory as CategoryModel;
use app\articles\logic\Category as CategoryLogic;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;
use app\common\model\Module;

class Index extends Admin
{
    protected $ModuleModel;
    protected $CategoryModel;
    protected $CategoryLogic;
    protected $ArticlesModel;
    protected $ArticlesLogic;

    public function __construct()
    {
        parent::__construct();
        $this->ModuleModel = new Module();
        $this->CategoryModel = new CategoryModel(); //分类模型
        $this->CategoryLogic = new CategoryLogic(); //分类逻辑
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
    }

    /**
     * 入口文章列表页
     */
    public function index()
    {
        $keyword = input('keyword', '', 'text');
        View::assign('keyword', $keyword);
        $category_id = input('category_id', 0, 'intval');
        View::assign('category_id', $category_id);
        $status = input('status') == null?'all':input('status');
        View::assign('status', $status);
        // 获取查询条件
        $map = $this->ArticlesLogic->getMap(0, $keyword, $category_id, $status);
        // 获取列表
        $lists = $this->ArticlesModel->getListByPage($map, 'sort DESC,id DESC', '*', 20);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->ArticlesLogic->formatData($val);
        }
        unset($val);

        View::assign('pager',$pager);
        View::assign('lists',$lists);
        // 获取分类树
        $category_list = $this->CategoryModel->getList([['status','=',1]], 999)->toArray();
        $category_tree = $this->CategoryLogic->categoryTree($category_list);
        View::assign('category_tree', $category_tree);
        // 显示渠道：移动端
        $channel = 'mobile';
        View::assign('channel', $channel);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        // 输出模板
        return View::fetch();
    }

    /**
     * 新增、编辑文章
     */
    public function edit()
    {

        // 输出模板
        View::fetch();
    }
}