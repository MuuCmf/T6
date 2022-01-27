<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Pc.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/1/27
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\controller\admin;
use app\admin\controller\Admin as MuuAdmin;
use app\common\model\Channel as ChannelModel;
use app\common\model\Module as ModuleModel;
use think\facade\Db;
use think\facade\View;

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
     * 前台公共导航
     */
    public function channel()
    {

        if (request()->isPost()) {

            $one = $_POST['nav'][1];

            if (count($one) > 0) {
                // 移除现有内容
                Db::execute('TRUNCATE TABLE ' . config('database.connections.mysql.prefix') . 'channel');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'type' => text($one['type'][$i]),
                        'app' => text($one['app'][$i]),
                        'title' => html($one['title'][$i]),
                        'url' => text($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => empty($one['target'][$i]) ? 0:intval($one['target'][$i]),
                        'status' => 1
                    );
                    $pid[$i] = $this->channelModel->insert($data[$i]);
                }

                cache('common_nav',null);

                return $this->success('修改成功');
            }
            return $this->error('导航至少存在一个。');

        } else {
            /* 获取频道列表 */
            $map[] = ['status', '>', -1];
            $list = $this->channelModel->where($map)->order('sort asc,id asc')->select()->toArray();


            // 获取应用模块列表
            $moduleModel = new ModuleModel();
            $module = $moduleModel->getAll(['is_setup' => 1]);
            View::assign('module', $module);
            View::assign('list', $list);

            $this->setTitle('导航管理');

            return View::fetch();
        }
    }
}