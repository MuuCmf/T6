<?php
namespace app\articles\controller\admin;

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
        return $this->success('success', $category_tree);
    }

    /**
     * 树结构返回
     */
    public function tree()
    {
        $category_tree = $this->CategoryModel->tree($this->shopid, 1);
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
            // 获取父级分类数据
            $parent = $this->CategoryModel->getDataById($pid);
        }
        
        $title = $id ? "编辑":"添加";

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

            return $this->success('success', $category_data);
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