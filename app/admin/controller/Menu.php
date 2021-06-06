<?php
namespace app\admin\Controller;

use app\admin\controller\Admin;
use think\facade\Db;

/**
 * 后台管理菜单控制器
 */
class Menu extends Admin {

    /**
     * 获取控制器菜单数组,二级菜单元素位于一级菜单的'_child'元素中
     */
    public function getMenus()
    {   
        dump($this->request);exit;
        $module = app('http')->getName();
        $controller = request()->controller();
        $action = request()->action();
        $menuModel = new \app\admin\model\Menu();
        $moduleModel = new \app\common\model\Module();

        $menus  =   session('ADMIN_MENU_LIST'.$controller);
        if (empty($menus)) {
            // 获取主菜单
            $where['pid'] = '0';
            //$where['hide'] = 0;
            
            $menus['main'] = $menuModel->getLists($where);
            
            $menus['main'] = $menus['main']->toArray();
            
            $menus['child'] = []; //设置子节点

            //当前菜单
            $current = $menuModel->whereRaw('`url` = "'.$module .'/'. $controller .'/'. $action .'"')->find();
            
            if ($current) {
                //获取顶级菜单数据
                $nav = $menuModel->getPath($current['id']);
                $nav_current_id = $nav[0]['id'];
                
                
                foreach ($menus['main'] as $key => $item) {

                    //如果是模块菜单获取模块信息
                    if($item['module'] != '' || !empty($item['module'])){
                        $module_info = $moduleModel->getModule($item['module']);
                    }
                    
                    if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                        $this->error(lang('_CLASS_CONTROLLER_ERROR_PARAM_',array('menus'=>$menus)));
                    }
                    if (stripos($item['url'], $module) !== 0) {
                        $item['url'] = $module . '/' . $item['url'];
                    }
                    // 判断主菜单权限
                    /*
                    if (!$this->checkRule($item['url'], 2, null)) {
                        unset($menus['main'][$key]);
                        continue;//继续循环
                    }*/

                    // 获取当前主菜单的子菜单项
                    if ($item['id'] == $nav_current_id) {
                        $menus['main'][$key]['class'] = 'active';
                        //生成child树
                        $groups = Db::name('Menu')->where(['pid'=>$item['id']])->distinct(true)->field("`group`")->order('sort asc')->select();

                        if ($groups) {
                            $groups = array_column($groups, 'group');
                        } else {
                            $groups = [];
                        }

                        //获取二级分类的合法url
                        $where = [];
                        $where['pid'] = $item['id'];
                        $where['hide'] = 0;
                        
                        $second_urls = $menuModel->getLists($where);

                        if (!$this->is_root) {
                            // 检测菜单权限
                            $to_check_urls = array();
                            foreach ($second_urls as $key => $to_check_url) {
                                if (stripos($to_check_url, $module) !== 0) {
                                    $rule = $module . '/' . $to_check_url;
                                } else {
                                    $rule = $to_check_url;
                                }
                                if ($this->checkRule($rule, AuthRuleModel::RULE_URL, null))
                                    $to_check_urls[] = $to_check_url;
                            }
                        }
                        // 按照分组生成子菜单树
                        $map = [];
                        foreach ($groups as $g) {
                            $map = array('group' => $g);
                            if (isset($to_check_urls)) {
                                if (empty($to_check_urls)) {
                                    // 没有任何权限
                                    continue;
                                } else {
                                    $map['url'] = array('in', $to_check_urls);
                                }
                            }
                            $map['pid'] = $item['id'];
                            $map['hide'] = 0;
                            
                            $menuList = Db::name('Menu')->where($map)->field('id,pid,title,url,icon,tip')->order('sort asc')->select();

                            $menus['child'][$g] = list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                        }
                    }
                }
            }
        }

        return $menus;
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
    public function edit($id = ''){
        
        if(request()->isPost()){
            $data = input('');
            $this->checkData($data);
            $res = model('admin/Menu')->editData($data);
            if($res){
                //记录行为
                action_log('update_menu', 'Menu', $data['id'], is_login());
                $this->success(lang('_SUCCESS_UPDATE_'), Cookie('__forward__'));
            } else {
                $this->error(lang('_FAIL_UPDATE_'));
            }
            
        } else {
            $info = [];
            /* 获取数据 */
            $info = model('admin/Menu')->where(['id'=>$id])->find();
            if(empty($info)){
                $map['id'] = input('pid');
                $info = model('Menu')->where($map)->field('module,pid,hide,is_dev,type')->find();
                $info['pid'] = input('pid');
            }
            $menus = collection(model('admin/Menu')->select())->toArray();
            $menus = model('common/Tree')->toFormatTree($menus,$title = 'title',$pk='id',$pid = 'pid',$root = '0');

            $menus = array_merge([0=>['id'=>'0','title_show'=>lang('_MENU_TOP_')]], $menus);

            $this->assign('Menus', $menus);
            $this->assign('Modules',model('Module')->getAll());
            if(false === $info){
                $this->error(lang('_ERROR_MENU_INFO_GET_'));
            }
            $this->assign('info', $info);
            $this->setTitle(lang('_MENU_BG_EDIT_'));
            return $this->fetch();
        }
    }
    /**
     * 检查数据合法性
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function checkData($data=[]) {

        if($data['title'] == '') {
            $this->error('菜单标题不能为空');
        }

        if($data['url'] == '') {
            $this->error('菜单链接不能为空');
        }
    }

    /**
     * 删除后台菜单
     */
    public function del(){
        $id = array_unique((array)input('id/a',[]));

        if (empty($id) ) {
            $this->error(lang('_ERROR_DATA_SELECT_').lang('_EXCLAMATION_'));
        }
        //判断是否有下级菜单
        $res =  Db::name('Menu')->where(['pid' => array('in', $id)])->select();
        if($res){
            $this->error(lang('_DELETE_SUBMENU_'));
        }
        //开始移除菜单
        $map = ['id' => ['in', $id]];
        if(Db::name('Menu')->where($map)->delete()){
            //记录行为
            action_log('update_menu', 'Menu', $id, is_login());
            $this->success(lang('_SUCCESS_DELETE_'));
        } else {
            $this->error(lang('_FAIL_DELETE_'));
        }
    }
    
    public function toogleHide($id,$value = 1){
        $this->editRow('Menu', array('hide'=>$value), array('id'=>$id),array('success' => '操作成功！', 'error' => '操作失败！'));
    }

    public function toogleDev($id,$value = 1){
        $this->editRow('Menu', array('is_dev'=>$value), array('id'=>$id),array('success' => '操作成功！', 'error' => '操作失败！'));
    }

    public function import(){
        if(request()->isPost()){
            $tree = input('post.tree');
            $lists = explode(PHP_EOL, $tree);

            if($lists == array()){
                $this->error(lang('_PLEASE_FILL_IN_THE_FORM_OF_A_BATCH_IMPORT_MENU,_AT_LEAST_ONE_MENU_'));
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
                            'is_dev'=>0,
                            'group'=>'',
                        ]);
                    }
                }
                $this->success(lang('_IMPORT_SUCCESS_'),Url('index?pid='.$pid));
            }
        }else{
            $this->setTitle(lang('_BATCH_IMPORT_BACKGROUND_MENU_'));
            $pid = (string)input('get.pid');
            $this->assign('pid', $pid);
            $data = Db::name('Menu')->where("id={$pid}")->field(true)->find();
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

    /**
     * 菜单排序
     */
    public function sort(){
        if(request()->isGet()){
            $ids = input('get.ids/a');
            $pid = input('get.pid','0');

            //获取排序的数据
            $map['hide']=0;
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }else{
                if($pid !== ''){
                    $map['pid'] = $pid;
                }
            }
            $list = Db::name('Menu')->where($map)->field('id,title')->order('sort asc')->select();

            $this->assign('list', $list);
            $this->setTitle(lang('_MENU_SORT_'));
            return $this->fetch();

        }elseif (request()->isPost()){
            $ids = input('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = Db::name('Menu')->where(['id'=>$value])->setField('sort', $key+1);
            }
            if($res !== false){
                $this->success(lang('_SORT_OF_SUCCESS_'));
            }else{
                $this->eorror(lang('_SORT_OF_FAILURE_'));
            }
        }else{
            $this->error(lang('_ILLEGAL_REQUEST_'));
        }
    }
}
