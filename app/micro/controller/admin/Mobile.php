<?php
namespace app\micro\controller\admin;

use app\admin\controller\Admin;
use app\common\model\Module;
use app\micro\model\MicroPage as PageModel;
use app\micro\logic\Page as PageLogic;
use think\facade\View;

class Mobile extends Admin
{
    protected $PageLogic;
    protected $PageModel;
    protected $CategoryModel;
    function __construct()
    {
        parent::__construct();
        $this->ModuleModel = new Module();
        $this->PageLogic = new PageLogic();
        $this->PageModel = new PageModel();

        // 获取各应用配置项
        // 获取classroom模块
        $classroom_config = (new \app\classroom\model\ClassroomConfig())->getDataByMap(['shopid' => 0]);
        $classroom_config = (new \app\classroom\logic\Config())->formatData($classroom_config);
        View::assign('classroom_config', $classroom_config);
        //获取分类树
        $category_tree = (new \app\classroom\model\ClassroomCategory())->getTree(1);
        View::assign('category_tree', $category_tree);
        
    }

    /**
     * @title 移动端页面列表
     */
    public function lists()
    {
        $keyword = input('get.keyword','');
        View::assign('keyword', $keyword);
        // 初始化查询条件
        $map = [
            ['shopid','=', 0],
            ['port_type', '=', 'mobile'],
            ['status','=', 1],
        ];
        if (!empty($keyword)){
            $map[] = ['title','like',"%{$keyword}%"];
        }
        // 每页显示数量
        $r = input('r', 15, 'intval');
        $lists = $this->PageModel->getListByPage($map,'id desc,create_time desc', '*', $r);
        $pager = $lists->render();
        View::assign('pager',$pager);
        $lists = $lists->toArray();
        foreach ($lists['data'] as &$item){
            $item = $this->PageLogic->formatData($item);
        }
        unset($item);
        
        if (request()->isAjax()){
            return $this->success('success',$lists);
        }
        View::assign('lists',$lists);
        // 获取分类树
        // $category_tree = $this->CategoryModel->getTree(0);
        // View::assign('category_tree', $category_tree);
        View::assign([
            'channel' => 'mobile'
        ]);
        // 设置title
        $this->setTitle('移动端自定义页面管理');
        // 输出页面
        return View::fetch();
    }

    /**
     * 移动端DIY
     */
    public function diy()
    {
        $id = input('id',0);
        if (request()->isAjax()){
            $params = input('post.');
            $id = empty($params['id'])? 0 : $params['id'];
            if(!empty($params['data'])){
                //反编译无需转义的组件内容
                foreach($params['data'] as &$v){
                    if($v['type'] == 'custom_text'){
                        $v['data']['content'] = htmlspecialchars_decode($v['data']['content']);
                    }
                }
                unset($v);
            }
            $data = [
                'id' => $id,
                'shopid' => $this->shopid,
                'app' => 'classroom',
                'title' => !empty($params['title'])?$params['title']:'页面标题未填写',
                'description' => !empty($params['description'])?$params['description']:'页面描述未填写',
                'data' => json_encode($params['data']),
                'port_type' => 'mobile',
                'footer_show' => intval($params['footer_show']),
                'type' => 0,
            ];
            $result = $this->PageModel->edit($data);
            if($result){
                return $this->success('保存成功');
            }else{
                return $this->error( '保存失败');
            }
        }

        //获取分类树
        $category_tree = $this->CategoryModel->getTree(0);
        View::assign('category_tree', $category_tree);
        //获取文章模块分类
		$article_plugin_setup = $this->ModuleModel->checkInstalled('articles');
		$article_category_tree = [];
		if($article_plugin_setup){
            $article_category_tree = (new \app\articles\model\ArticlesCategory())->getTree(1);
		}
        View::assign('article_category_tree', $article_category_tree);


        //页面数据
        $page_data = $this->PageModel->find($id);
        if (!empty($page_data)){
            $page_data = $page_data->toArray();
            $page_data = $this->PageLogic->formatData($page_data);
            $page_data = $this->PageLogic->handlingNoParamJson($page_data);
        }else{
            $page_data = [];
        }
        
        View::assign([
            'page_data' => $page_data,
            'icon_list' => $this->PageLogic->getIconLists()
        ]);
        // 链接至参数
        $link_list = $this->PageLogic->linkParams();
        View::assign('link_list', $link_list);
        
        // 获取无图标路径
        $no_icon = request()->domain() . '/static/common/images/diy/noimg.png';
        View::assign('no_icon', $no_icon);
        // 设置title
        $this->setTitle('移动端自定义页面DIY');
        // 输出页面
        return view();
    }

    /**
     * 设置首页
     */
    public function setHome(){
        $id = input('get.id');
        $port_type = input('get.port_type');

        //移除首页项
        $this->PageModel->where(['port_type' => $port_type])->update([
            'home' => 0,
            'update_time' => time()
        ]);

        //设置首页
        $res = $this->PageModel->update([
            'id' => $id,
            'home' => 1
        ]);
        if($res){
            return $this->success('状态更新成功！');
        }else{
            return $this->error('状态更新失败！');
        }
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

        $res = $this->PageModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }


}