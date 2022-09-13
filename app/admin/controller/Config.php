<?php
namespace app\admin\controller;

use think\facade\View;
use think\facade\Cache;
use app\admin\model\Config as MuuConfigModel;
// +----------------------------------------------------------------------
// | TODO:系统设置 站点信息内容包含：站点基本信息 联络和客服信息 版权信息
// +----------------------------------------------------------------------
/**
 * 后台配置控制器
 */
class Config extends Admin
{
    protected $ConfigModel;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->ConfigModel = new MuuConfigModel();
    }

    // 获取某个标签的配置参数
    public function group()
    {
        if (request()->isPost()) {
            $config = input('post.config/a');
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    $this->ConfigModel->where($map)->save(['value' => $value]);
                }
            }
            // 清除缓存
            Cache::delete(request()->host() . '_MUUCMF_SYS_CONFIG_DATA', null);
            return $this->success('保存成功',$config, cookie('__forward__'));

        }else{
            $id = input('id', 1,'intval');
            View::assign('id', $id);
            // 配置分组
            $type = config('system.CONFIG_GROUP_LIST');
            View::assign('type', $type);
            // 配置项列表
            $list = $this->ConfigModel->where(['status' => 1, 'group' => $id])->field('id,name,title,extra,value,group,remark,type')->order('sort asc')->select()->toArray();

            View::assign('list', $list);
            // 设置页面Title
            $this->setTitle($type[$id] . '设置');
            // 记录当前列表页的cookie
            cookie('__forward__', $_SERVER['REQUEST_URI']);
            return View::fetch();
        }
    }

    /**
     * 系统配置参数管理
     */
    public function list()
    {
        $group = input('group', 0);
        /* 查询条件初始化 */
        $map = [];
        $map[] = ['status','=', 1]; 
        if (isset($_GET['group'])) {
            $map[] = ['group','=',$group];
        }
        if (isset($_GET['name'])) {
            $map[] = ['name','like', '%' . (string)input('name') . '%'];
        }

        list($list,$page) = $this->commonLists('Config', $map, 'sort,id');
        $list = $list->toArray()['data'];

        View::assign('group', config('system.CONFIG_GROUP_LIST'));
        View::assign('group_id', input('get.group', 0));
        View::assign('list', $list);
        View::assign('page', $page);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->setTitle('配置管理');
        // 输出页面
        return View::fetch();
    }

    /**
     * 编辑系统配置
     */
    public function edit()
    {
        if (request()->isPost()) {
            $data = input('');
            //验证器
            $validate = $this->validate(
                [
                    'name'  => $data['name'],
                    'title'   => $data['title'],
                ],[
                    'name'  => 'require|max:36',
                    'title'   => 'require',
                ],[
                    'name.require' => '标识必须填写',
                    'name.max'     => '标识最多不能超过36个字符',
                    'title.require'   => '标题必须填写', 
                ]
            );
            if(true !== $validate){
                // 验证失败 输出错误信息
                return $this->error($validate);
            }

            $data['status'] = 1;//默认状态为启用

            $res = $resId = $this->ConfigModel->edit($data);
            if($res){
                Cache::delete(request()->host() . '_MUUCMF_SYS_CONFIG_DATA', null);
                //记录行为
                action_log('update_config', 'config', $resId, is_login());
                return $this->success('操作成功', $res, Cookie('__forward__'));
            }else{
                return $this->error('操作失败');
            }
            
        } else {
            $id = input('id', 0, 'intval');
            /* 获取数据 */
            if($id != 0){
                $info = $this->ConfigModel->find($id);
            }else{
                $info = [];
            }
            
            View::assign('type', get_config_type_list());
            View::assign('group', config('system.CONFIG_GROUP_LIST'));
            View::assign('info', $info);
            $this->setTitle('编辑配置');
            // 输出页面
            return View::fetch();
        }
    }

    /**
     * 删除配置
     */
    public function del()
    {
        $id = array_unique((array)input('id', 0));

        if (empty($id)) {
            $this->error('参数错误');
        }

        if ($this->ConfigModel->where('id','in', $id)->delete()) {
            Cache::delete(request()->host() . '_MUUCMF_SYS_CONFIG_DATA', null);
            //记录行为
            action_log('update_config', 'config', $id, is_login());
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

}
