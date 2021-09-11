<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\admin\model\Menu as MenuModel;
use app\common\model\Module as ModuleModel;
use app\common\model\Tree;

/**
 * 后台管理菜单控制器
 */
class Menu extends Admin {

    protected $menuModel;
    protected $moduleModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->menuModel = new MenuModel();
        $this->moduleModel = new moduleModel();
    }

    /**
     * 后台菜单首页
     * @return none
     */
    public function index(){
        $title = input('title','','text');
        $pid  = input('pid','0','text');
        //获取上级数据
        if($pid){
            $where['id'] = $pid;
            $data = $this->menuModel->where($where)->find();
            View::assign('data',$data);
        }
        View::assign('pid',$pid);
        
        if($title){
            $map['title'] = ['like','%'.$title.'%'];
        }
        
        $map = [];
        //$map['pid'] =   $pid;
        $list = $this->menuModel->where($map)->order('sort asc')->select()->toArray();
        foreach($list as &$val){
            $val = $this->menuModel->handle($val);
        }
        unset($val);
        // 转树结构
        $list = list_to_tree($list, 'id', 'pid', '_child', '0');
        
        View::assign('list',$list);

        $this->setTitle('后台菜单管理');

        return View::fetch();
    }

    public function index2(){
        $title = input('title','','text');
        $pid  = input('pid','0','text');
        //获取上级数据
        if($pid){
            $where['id'] = $pid;
            $data = $this->menuModel->where($where)->find();
            View::assign('data',$data);
        }
        View::assign('pid',$pid);
        
        if($title){
            $map['title'] = ['like','%'.$title.'%'];
        }
        
        $map = [];
        //$map['pid'] =   $pid;
        $list = $this->menuModel->where($map)->order('sort asc')->select()->toArray();
        foreach($list as &$val){
            $val = $this->menuModel->handle($val);
        }
        unset($val);
        // 转树结构
        $list = list_to_tree($list, 'id', 'pid', '_child', '0');
        
        dump($list);
        View::assign('list',$list);

        $this->setTitle('后台菜单管理');

        return View::fetch();
    }


    /**
     * 后台菜单列表
     * @return none
     */
    public function list(){
        $title = input('title','','text');
        $pid  = input('pid','0','text');
        $module = input('module','','text');
        
        if($title){
            $map['title'] = ['like','%'.$title.'%'];
        }
        if($module){
            $map['module'] = $module;
        }
        $map['pid'] =   $pid;
        $list       =   Db::name("Menu")->where($map)->order('sort asc,id asc')->select();

        //输出
        return $this->result(200,'SUCCESS',$list);
    }

    /**
     * 新增/编辑配置
     */
    public function edit(){
        
        if(request()->isPost()){
            $data = input('');
            if($data['title'] == '') {
                return $this->error('菜单标题不能为空');
            }
            if($data['url'] == '') {
                return $this->error('菜单链接不能为空');
            }

            $menuModel = new MenuModel();
            $res = $menuModel->edit($data);
            if($res){
                //记录行为
                action_log('update_menu', 'Menu', $data['id'], is_login());
                return $this->success('保存成功',url('index'));
            } else {
                return $this->error('保存失败');
            }
            
        } else {
            $id = input('id','0','text');
            $pid = input('pid','0','text');
            View::assign('pid', $pid);
            $info = [];
            /* 获取数据 */
            $menuModel = new MenuModel();
            $info = $menuModel->where(['id'=>$id])->find();

            if(empty($info)){
                $map['id'] = input('pid');
                $info = $menuModel->where($map)->field('module,pid,hide,type')->find();
                $info['pid'] = input('pid','0','text');
            }
            View::assign('info', $info);

            $menus = $menuModel->order('sort asc,id asc')->select()->toArray();
            $tree = new Tree();
            $menus = $tree->toFormatTree($menus,$title = 'title',$pk='id',$pid = 'pid',$root = '0');
            $menus = array_merge([
                0 => ['id'=>'0','title_show'=>'顶级菜单']
            ], $menus);

            View::assign('Menus', $menus);
            $moduleModel = new ModuleModel();
            View::assign('Modules',$moduleModel->getAll());

            $this->setTitle('菜单编辑');

            return View::fetch();
        }
    }

    /**
     * 删除后台菜单
     */
    public function del(){
        $id = array_unique((array)input('id/a',[]));

        if (empty($id) ) {
            return $this->error('参数错误');
        }
        //判断是否有下级菜单
        $res =  Db::name('Menu')->where('pid', 'in', $id)->select()->toArray();
        
        if(!empty($res)){
            return $this->error('下级菜单不为空');
        }
        //开始移除菜单
        if(Db::name('Menu')->where('id', 'in', $id)->delete()){
            //记录行为
            action_log('update_menu', 'Menu', $id, is_login());
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }
    
    /**
     * 菜单导入
     */
    public function import(){
        if(request()->isPost()){
            $tree = input('post.tree');
            $lists = explode(PHP_EOL, $tree);

            if($lists == array()){
                return $this->error('请按格式填写批量导入的菜单，至少一个菜单');
            }else{
                $pid = input('post.pid');
                foreach ($lists as $key => $value) {
                    $record = explode('|', $value);
                    if(count($record) == 2){
                        Db::name('Menu')->insert([

                            'id' =>create_guid(),
                            'title'=>$record[0],
                            'url'=>$record[1],
                            'pid'=>$pid,
                            'sort'=>0,
                            'hide'=>0,
                            'tip'=>'',
                            'group'=>'',
                        ]);
                    }
                }
                return $this->success('导入成功','',url('index',['pid' => $pid]));
            }
        }else{
            $this->setTitle('菜单导入');
            $pid = (string)input('get.pid');
            View::assign('pid', $pid);
            $data = Db::name('Menu')->where('id','=', $pid)->find();

            View::assign('data', $data);
            return View::fetch();
        }
    }

    /**
     * 菜单排序
     */
    public function sort(){
        if(request()->isGet()){
            $this->setTitle('菜单排序');
            $ids = input('get.ids/a');
            $pid = input('get.pid','0');

            //获取排序的数据
            $map['hide']=0;
            if(!empty($ids)){
                $map['id'] = ['in',$ids];
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = Db::name('Menu')->where($map)->field('id,title')->order('sort asc')->select();

            View::assign('list', $list);
            
            return View::fetch();

        }elseif (request()->isPost()){
            $ids = input('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = Db::name('Menu')->where(['id'=>$value])->setField('sort', $key+1);
            }
            if($res !== false){
                return $this->success('排序成功');
            }else{
                return $this->error('排序失败');
            }
        }else{
            return $this->error('非法请求');
        }
    }
}
