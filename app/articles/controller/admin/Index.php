<?php
namespace app\articles\controller\admin;

use think\facade\View;
use app\admin\controller\Admin as MuuAdmin;
use app\articles\model\ArticlesCategory as CategoryModel;
use app\common\model\Module;

class Index extends MuuAdmin
{
    protected $ModuleModel;

    public function __construct()
    {
        parent::__construct();

        $this->ModuleModel = new Module();
        $this->CategoryModel = new CategoryModel(); //分类模型
    }

    /**
     * 入口文章列表页
     */
    public function index()
    {

        // 输出模板
        View::fetch();
    }

    public function edit()
    {
        
        // 输出模板
        View::fetch();
    }
}