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
    protected $channel;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->channel = new ChannelModel();
    }
    /**
     * 前台公共导航
     */
    public function common()
    {
        
        if (request()->isPost()) {

            $one = $_POST['nav'][1];
            if (count($one) > 0) {
                Db::execute('TRUNCATE TABLE ' . config('database.connections.mysql.prefix') . 'channel');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'pid' => 0,
                        'title' => html($one['title'][$i]),
                        'url' => text($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => empty($one['target'][$i]) ? 0:intval($one['target'][$i]),
                        'band_text' => text($one['band_text'][$i]),
                        'status' => 1
                    );
                    $pid[$i] = $this->channel->insert($data[$i]);
                }

                if(!empty($_POST['nav'][2])){
                    $two = $_POST['nav'][2];

                    for ($j = 0; $j < count(reset($two)); $j++) {
                        $data_two[$j] = array(
                            'pid' => $pid[$two['pid'][$j]],
                            'title' => html($two['title'][$j]),
                            'url' => text($two['url'][$j]),
                            'sort' => intval($two['sort'][$j]),
                            'target' => intval($two['target'][$j]),
                            'band_text' => text($two['band_text'][$j]),
                            'status' => 1
                        );
                        $res[$j] = $this->channel->insert($data_two[$j]);
                    }
                }
                
                cache('common_nav',null);
                $this->success('修改成功');
            }
            $this->error('导航至少存在一个。');

        } else {
            /* 获取频道列表 */
            $map[] = ['status', '>', -1];
            $map[] = ['pid', '=', 0];
            $list = $this->channel->where($map)->order('sort asc,id asc')->select();
            foreach ($list as $k => &$v) {
                $module = Db::name('Module')->where(['entry' => $v['url']])->find();
                $v['module_name'] = $module['name'];
            }
            unset($k, $v);

            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module', $module);
            View::assign('list', $list);

            $this->setTitle('导航管理');

            return View::fetch();
        }
    }

    /**
     * 用户导航
     * @return [type] [description]
     */
    public function user(){
        $Channel = Db::name('UserNav');
        if (request()->isPost()) {
            $one = $_POST['nav'][1];
            if (count($one) > 0) {
                Db::execute('TRUNCATE TABLE ' . config('database.connections.mysql.prefix') . 'user_nav');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'title' => text($one['title'][$i]),
                        'url' => text($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => intval($one['target'][$i]),
                        //'color' => text($one['color'][$i]),
                        'band_text' => text($one['band_text'][$i]),
                        'status' => 1
                    );
                    $pid[$i] = $Channel->insert($data[$i]);
                }
                cache('common_user_nav',null);
                $this->success('修改成功');
            }
            $this->error('导航至少存在一个');
        } else {
            $this->setTitle('导航管理');
            /* 获取频道列表 */
            $map[] = ['status','>', -1];
            $list = $Channel->where($map)->order('sort asc,id asc')->select()->toArray();
            foreach ($list as $k => &$v) {
                $module = Db::name('Module')->where(['entry' => $v['url']])->find();
                $v['module_name'] = $module['name'];
                unset($key, $val);
            }
            unset($k, $v);

            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module', $module);
            View::assign('list', $list);

            return View::fetch();
        }
    }
}
