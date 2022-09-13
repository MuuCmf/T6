<?php
namespace app\articles\controller\admin;

use think\facade\View;
use app\articles\model\ArticlesCategory as CategoryModel;
use app\articles\logic\Category as CategoryLogic;

class Category extends Admin
{
    protected $CategoryModel;
    protected $CategoryLogic;

    public function __construct()
    {
        parent::__construct();

        $this->CategoryModel = new CategoryModel(); //分类模型
        $this->CategoryLogic = new CategoryLogic(); //分类逻辑
    }

    /**
     * 分类管理页
     */
    public function lists()
    {
        $category_tree = $this->CategoryModel->tree($this->shopid, [0,1]);
        if(request()->isAjax()){
            return $this->success('success', $category_tree);
        }
        
        View::assign('category_tree',$category_tree);

        $this->setTitle('分类列表');
        //输出页面
    	return View::fetch();
    }

    /**
     * 树结构返回
     */
    public function tree()
    {
        $category_tree = $this->CategoryModel->tree($this->shopid, 0);
        return $this->success('success', $category_tree);
    }

    /**
     * 分类添加/编辑
     * @param int $id
     * @param int $pid
     */
    public function edit()
    {   
        $id = input('id', 0, 'intval');
        $pid = input('pid', 0, 'intval');
        if(!empty($pid)){
            View::assign('pid', $pid);
            // 获取父级分类数据
            $parent = $this->CategoryModel->getDataById($pid);
            View::assign('parent', $parent);
        }
        

        $title = $id ? "编辑":"添加";
        View::assign('title', $title);

        if (request()->isPost()) {
            $data = input();
            $res = $this->CategoryModel->edit($data);

            if ($res) {
                if(!empty($data['pid'])){
                    $url = url('lists', ['pid' => $data['pid']]);
                }else{
                    $url = url('lists');
                }
                
                return $this->success($title.'成功','', $url);
            } else {
                return $this->error($title.'失败' . $this->CategoryModel->getError());
            }

        } else {
            // 获取顶级分类树
            $category= $this->CategoryModel->tree($this->shopid, 1);
            View::assign('category',$category);

            // 初始化数据结构
            $category_data = [
                'id' => 0,
                'title' => '',
                'description' => '',
                'cover' => '',
                'sort' => 0,
                'status' => 1,
            ];
            if (!empty($id)) {
                $category_data = $this->CategoryModel->getDataById($id);
            }
            View::assign('category_data',$category_data);

            $this->setTitle($title.'分类');
            // 输出页面
            return View::fetch();
        }
    }

    /**
     * 设置状态
     */
    public function status()
    {   
        $ids = input('ids/a');

        !is_array($ids)&&$ids=explode(',',$ids);
        $ids = array_unique((array)$ids);
        
        $status = input('status', 0,'intval');
        
        //初始化更新数据
        $data = [];
        if($status == 0){//禁用
            $data['status'] = 0;
            $title = '禁用';
        }
        if($status == 1){//启用
            $data['status'] = 1;
            $title = '启用';
        }
        if($status == -1){//删除
            $data['status'] = -1;
            $title = '删除';
        }

        $res = $this->CategoryModel->where('id' ,'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

}