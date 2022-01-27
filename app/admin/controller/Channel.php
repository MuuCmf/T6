<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\common\model\Module as ModuleModel;
use app\common\model\Channel as ChannelModel;
/**
 * 后台频道控制器
 */
class Channel extends Admin
{
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
     * 用户导航
     * @return [type] [description]
     */
    public function user(){

        if (request()->isPost()) {
            $one = $_POST['nav'][1];
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
                cache('common_user_nav',null);
                return $this->success('修改成功');
            }
            return $this->error('导航至少存在一个');
        } else {
            $this->setTitle('导航管理');
            /* 获取频道列表 */
            $map[] = ['status','>', -1];
            $list = Db::name('UserNav')->where($map)->order('sort asc,id asc')->select()->toArray();
            
            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module', $module);
            View::assign('list', $list);

            return View::fetch();
        }
    }
}
