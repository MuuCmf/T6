<?php
namespace app\articles\controller\api;

use app\articles\model\ArticlesCategory as CategoryModel;
use app\articles\logic\Category as CategoryLogic;

/**
 * 分类接口
 */
class Category extends Base
{   
    protected $CategoryModel;
    protected $CategoryLogic;

    public function __construct()
    {
        parent::__construct();
        $this->CategoryModel = new CategoryModel(); //分类模型
        $this->CategoryLogic = new CategoryLogic(); //分类模型
    }
    
    /**
     * 树结构返回
     */
    public function tree()
    {
        $category_tree = $this->CategoryModel->tree($this->shopid, 1);
        return $this->success('success', $category_tree);
    }
}