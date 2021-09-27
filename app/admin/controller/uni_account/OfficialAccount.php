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
            $config = input('post.');
            // 清理缓存
            cache('MUUCMF_EXT_CONFIG_DATA', null);

            return $this->success('保存成功',$config, 'refresh');

        }else{
            //查询微信平台配置

            $builder = new AdminConfigBuilder();
            $builder->title('公众号配置')->suggest('基于微信各项参数配置');
            $data = $this->uniAccountModel->where(['status' => 1, 'group' => 2])->field('id,name,title,extra,value,group,remark,type')->order('sort asc')->select()->toArray();

            // 腾讯云COS参数配置
            $builder
                //->keyText('COS_TENCENT_APPID', 'APPID', 'APPID 是您项目的唯一ID.')
                ->keyText('COS_TENCENT_SECRETID', 'SecretID', 'SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('COS_TENCENT_SECRETKEY', 'SecretKEY', 'SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('COS_TENCENT_BUCKET', 'Bucket', 'Bucket名称.')
                ->keyText('COS_TENCENT_REGION', 'Region', 'Bucket所在区域，格式 如：ap-beijing.')
                ->keyText('COS_TENCENT_BUCKET_DOMAIN', 'Bucket域名', '腾讯云支持用户自定义访问域名。注：url开头加http://或https://结尾不加 ‘/’例：http://abc.com.')
                ->group('小程序', [
                    //'COS_TENCENT_APPID',
                    'COS_TENCENT_SECRETID',
                    'COS_TENCENT_SECRETKEY',
                    'COS_TENCENT_BUCKET',
                    'COS_TENCENT_REGION',
                    'COS_TENCENT_BUCKET_DOMAIN'
                ]);

            $builder->data($data);
            $builder->buttonSubmit();
            $builder->display();
        }
    }
}