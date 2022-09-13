<?php
namespace app\channel\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\common\model\Channel as ChannelModel;
use app\common\model\Module as ModuleModel;
use think\facade\Db;
use think\facade\View;
use think\Exception;

class Pc extends MuuAdmin{
    protected $channelModel;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->channelModel = new ChannelModel();
        
    }

    /**
     * 顶部通用导航
     */
    public function navbar()
    {

        if (request()->isPost()) {

            $nav = $_POST['nav'];
            // 启动事务
            Db::startTrans();
            try {
                if (count($nav) > 0) {
                    $this->channelModel->where([
                        'block' => 'navbar',
                    ])->delete();
                    for ($i = 0; $i < count(reset($nav)); $i++) {
                        $data[$i] = [
                            'id' => create_guid(),
                            'block' => 'navbar',
                            'type' => text($nav['type'][$i]),
                            'app' => text($nav['app'][$i]),
                            'title' => html($nav['title'][$i]),
                            'url' => text($nav['url'][$i]),
                            'sort' => intval($i),
                            'target' => empty($nav['target'][$i]) ? 0:intval($nav['target'][$i]),
                            'status' => 1,
                            'color' => empty($nav['color'][$i]) ?? '',
                            'icon' => empty($nav['icon'][$i]) ?? ''
                        ];
                    }
                    $res = $this->channelModel->insertAll($data);
                    if($res){
                        // 提交事务
                        Db::commit();
                        return $this->success('修改成功',$res);
                    }
                }else{
                    throw new Exception('导航至少存在一个。');
                }
                
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return $this->error($e->getMessage());
            }
            
            

        } else {
            /* 获取频道列表 */
            $map[] = ['status', '=', 1];
            $map[] = ['block', '=', 'navbar'];
            $list = $this->channelModel->where($map)->order('sort asc')->select()->toArray();
            View::assign('list', $list);
            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module_list = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module_list', $module_list);
            
            $this->setTitle('导航管理');

            return View::fetch();
        }
    }

    /**
     * 底部快捷导航
     */
    public function footer()
    {
        if (request()->isPost()) {

            $nav = $_POST['nav'];

            // 启动事务
            Db::startTrans();
            try {
                // 移除现有内容
                $this->channelModel->where([
                    'block' => 'footer',
                ])->delete();
                for ($i = 0; $i < count(reset($nav)); $i++) {
                    $data[$i] = [
                        'id' => create_guid(),
                        'block' => 'footer',
                        'type' => text($nav['type'][$i]),
                        'app' => text($nav['app'][$i]),
                        'title' => html($nav['title'][$i]),
                        'url' => text($nav['url'][$i]),
                        'sort' => intval($i),
                        'target' => empty($nav['target'][$i]) ? 0:intval($nav['target'][$i]),
                        'status' => 1,
                        'color' => empty($nav['color'][$i]) ?? '',
                        'icon' => empty($nav['icon'][$i]) ?? ''
                    ];
                }

                $res = $this->channelModel->insertAll($data);
                if($res){
                    // 提交事务
                    Db::commit();
                    return $this->success('修改成功',$res);
                }
                
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return $this->error($e->getMessage());
            }

        } else {
            /* 获取频道列表 */
            $map[] = ['status', '>', -1];
            $map[] = ['block', '=', 'footer'];
            $list = $this->channelModel->where($map)->order('sort asc')->select()->toArray();
            View::assign('list', $list);
            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module_list = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module_list', $module_list);
            
            $this->setTitle('导航管理');

            return View::fetch();
        }
    }


    /**
     * 用户导航
     * @return [type] [description]
     */
    public function user(){

        if (request()->isPost()) {
            $one = $_POST['nav'][1];
            // 启动事务
            Db::startTrans();
            try {
                if (count($one) > 0) {
                    Db::execute('TRUNCATE TABLE ' . config('database.connections.mysql.prefix') . 'user_nav');

                    for ($i = 0; $i < count(reset($one)); $i++) {
                        $data[$i] = array(
                            'type' => text($one['type'][$i]),
                            'app' => text($one['app'][$i]),
                            'title' => text($one['title'][$i]),
                            'url' => text($one['url'][$i]),
                            'sort' => intval($one['sort'][$i]),
                            'target' => intval($one['target'][$i]),
                            'status' => 1
                        );
                        $pid[$i] = Db::name('UserNav')->insert($data[$i]);
                    }
                    // 提交事务
                    Db::commit();
                    cache(request()->domain() . '_muucmf_user_nav',null);
                    return $this->success('修改成功');
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return $this->error($e->getMessage());
            }
        } else {
            $this->setTitle('导航管理');
            /* 获取频道列表 */
            $map[] = ['status','>', -1];
            $list = Db::name('UserNav')->where($map)->order('sort asc,id asc')->select()->toArray();
            View::assign('list', $list);
            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module_list = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module_list', $module_list);
            
            return View::fetch();
        }
    }
}