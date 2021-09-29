<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: OfficialAccount.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/23
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller\uni_account;
use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin;
use app\common\model\UniAccount;
use think\facade\Db;
use think\facade\View;

/**
 * 公众号管理
 * Class OfficialAccount
 * @package app\admin\controller
 */
class OfficialAccount extends Admin {
    private  $uniAccountModel;
    function __construct()
    {
        parent::__construct();
        $this->uniAccountModel = new UniAccount();
    }

    public function menu(){
        return view();
    }
    /**
     * 存储配置
     */
    public function index()
    {
        if (request()->isPost()) {
            $config = input('post.config');
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    $this->uniAccountModel->where($map)->update(['value' => $value]);
                }
            }
            // 清理缓存
            cache('MUUCMF_UNI_ACCOUNT_CONFIG_DATA', null);

            return $this->success('保存成功',$config, 'refresh');

        }else{
            //查询微信平台配置
            $list = $this->uniAccountModel->where(['group' => 'wechat_official_account','status' => 1])->order('sort','asc')->select()->toArray();
            View::assign('list', $list);
            return view();
        }
    }
}