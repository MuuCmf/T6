<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: MiniProgram.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/10/14
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\controller\admin;
use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin as MuuAdmin;
use app\common\model\Module;
use app\unions\model\WechatMpConfig;

class WechatMiniProgram extends MuuAdmin{
    private $miniProgramModel;
    private $shopid;
    function __construct()
    {
        parent::__construct();
        $this->miniProgramModel = new WechatMpConfig();
        $this->shopid = 0;
    }

    /**
     * 商户小程序配置
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        if (request()->isAjax()){
            $params = input('post.');
            $data = [
                'id' => 0,
                'shopid' => $this->shopid,
                'title' => $params['title'],
                'description' => $params['description'],
                'appid' => $params['appid'],
                'secret' => $params['secret'],
                'originalid' => $params['originalid'],
            ];
            $map = [
                ['shopid' ,'=' ,$this->shopid],
            ];
            $id = $this->miniProgramModel->where($map)->value('id');
            if ($id){
                $data['id'] = $id;
            }
            $this->miniProgramModel->edit($data);
            $this->success('保存成功');
        }else{
            //查询分组数据
            //查询数据
            $config = $this->miniProgramModel->where([
                ['shopid' ,'=' ,$this->shopid],

            ])->find()->toArray();
            $builder = new AdminConfigBuilder();
            $builder->title('微信小程序配置')->suggest('基于第三方授权各项参数配置');

            $builder
                ->keyText('title', '小程序名称', '小程序名称.')
                ->keyText('appid', 'APPID', 'APPID是小程序的ID，请您妥善保管.')
                ->keyText('secret', 'AppSecret', 'AppSecret是小程序的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('originalid', '原始ID', '小程序原始ID')
                ->keyTextArea('description', '小程序描述', '小程序描述')
                ->group('微信小程序配置', [
                    'title',
                    'appid',
                    'secret',
                    'originalid',
                    'description',
                ]);
            $builder->data($config);
            $builder->buttonSubmit();
            $builder->display();
        }
    }
}