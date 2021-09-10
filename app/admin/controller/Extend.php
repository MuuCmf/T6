<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\admin\model\ExtendConfig as MuuExtendConfigModel;

/**
 * 后台配置控制器
 */
class Extend extends Admin
{
    protected $moduleModel;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->extendConfigModel = new MuuExtendConfigModel();
    }

    /**
     * 短信发送参数配置
     */
    public function sms() {

        if (request()->isPost()) {
            $data = input('');
            

        }else{
            $builder = new AdminConfigBuilder();
            $data = [];
            $builder->title('短信配置')->suggest('基于第三方短信发送各项参数配置');
    
            $opt = ['local' => '阿里云'];
            
            $builder->keySelect('PICTURE_UPLOAD_DRIVER', '图片', lang('_PICTURE_UPLOAD_DRIVER_'), $opt);
            $builder->keySelect('DOWNLOAD_UPLOAD_DRIVER', '文件', lang('_ATTACHMENT_UPLOAD_DRIVER_'), $opt);
            $builder->group('存储', ['PICTURE_UPLOAD_DRIVER', 'DOWNLOAD_UPLOAD_DRIVER']);
            
            $opt = array('none' => '无');
            $builder
                ->keySelect('SMS_HOOK', '短信平台', lang('_SMS_SEND_SERVICE_PROVIDERS_NEED_TO_INSTALL_THE_PLUG-IN_'), $opt)
                ->keyText('SMS_SIGN', 'appid', lang('_SMS_PLATFORM_SIGN_CONT_'))
                ->keyDefault('SMS_SIGN','【MuuCmf】');
    
            $builder->group('短信', ['SMS_HOOK', 'SMS_SIGN']);
    
            $builder->data($data);
            $builder->buttonSubmit();
            $builder->display();
        }
        
    }

    /**
     * 存储配置
     */
    public function store()
    {
        if (request()->isPost()) {
            $config = input('post.');
            //dump($config);exit;
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    Db::name('ExtendConfig')->where($map)->save(['value' => $value]);
                }
            }
            cache('MUUCMF_EXT_CONFIG_DATA', null);
    
            return $this->success('保存成功',$config, 'refresh');

        }else{

            $list = Db::name("ExtendConfig")->where(['status' => 1])->field('id,name,title,extra,value,group,remark,type')->order('sort asc')->select()->toArray();
            $list = $this->extendConfigModel->lists();

            $builder = new AdminConfigBuilder();
            $builder->title('存储配置')->suggest('基于第三方云存储各项参数配置');
            
            // 基础配置
            $opt = ['local' => '本地','aliyun' => '阿里云', 'tencent' => '腾讯云'];
            $builder
                ->keySelect('PICTURE_UPLOAD_DRIVER', '图片', '图片上传驱动', $opt)
                ->keySelect('FILE_UPLOAD_DRIVER', '文件', '文件上传驱动', $opt)
                ->group('基础配置', [
                    'PICTURE_UPLOAD_DRIVER', 
                    'FILE_UPLOAD_DRIVER'
                ]);
            
            // 阿里云OSS参数配置
            $builder
                ->keyText('OSS_ALIYUN_ACCESSKEYID', 'AccessKeyID', 'Access Key ID是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('OSS_ALIYUN_ACCESSKEYSECRET', 'AccessKeySecret', 'Access Key Secret是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('OSS_ALIYUN_ENDPOINT', 'Endpoint', '如：oss-cn-beijing.aliyuncs.com.')
                ->keyText('OSS_ALIYUN_BUCKET', 'Bucket', 'Bucket.')
                ->keyText('OSS_ALIYUN_BUCKET_DOMAIN', 'Bucket域名', 'Bucket域名.')
                ->group('阿里云OSS', [
                    'OSS_ALIYUN_ACCESSKEYID', 
                    'OSS_ALIYUN_ACCESSKEYSECRET',
                    'OSS_ALIYUN_ENDPOINT',
                    'OSS_ALIYUN_BUCKET',
                    'OSS_ALIYUN_BUCKET_DOMAIN'
                ]);

            // 腾讯云COS参数配置
            $builder
                //->keyText('COS_TENCENT_APPID', 'APPID', 'APPID 是您项目的唯一ID.')
                ->keyText('COS_TENCENT_SECRETID', 'SecretID', 'SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('COS_TENCENT_SECRETKEY', 'SecretKEY', 'SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('COS_TENCENT_BUCKET', 'Bucket', 'Bucket名称.')
                ->keyText('COS_TENCENT_REGION', 'Region', 'Bucket所在区域，格式 如：ap-beijing.')
                ->keyText('COS_TENCENT_BUCKET_DOMAIN', 'Bucket域名', '腾讯云支持用户自定义访问域名。注：url开头加http://或https://结尾不加 ‘/’例：http://abc.com.')
                ->group('腾讯云COS', [
                    //'COS_TENCENT_APPID', 
                    'COS_TENCENT_SECRETID',
                    'COS_TENCENT_SECRETKEY',
                    'COS_TENCENT_BUCKET',
                    'COS_TENCENT_REGION',
                    'COS_TENCENT_BUCKET_DOMAIN'
                ]);

            $builder->data($list);
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    /**
     * 扩展配置管理
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

        list($list,$page) = $this->commonLists('ExtendConfig', $map, 'sort,id');
        $list = $list->toArray()['data'];
        
        View::assign('group', config('extend.GROUP_LIST'));
        View::assign('group_id', input('get.group', 0));
        View::assign('list', $list);
        View::assign('page', $page);

        $this->setTitle('配置管理');

        return View::fetch();
    }

    /**
     * 编辑系统配置
     */
    public function edit($id = 0)
    {
        if (request()->isPost()) {
            $data = input('');
            //验证器
            $validate = $this->validate(
                [
                    'name'  => $data['name'],
                    'title'   => $data['title'],
                ],[
                    'name'  => 'require|max:32',
                    'title'   => 'require',
                ],[
                    'name.require' => '标识必须填写',
                    'name.max'     => '标识最多不能超过32个字符',
                    'title.require'   => '标题必须填写', 
                ]
            );
            if(true !== $validate){
                // 验证失败 输出错误信息
                return $this->error($validate);
            }

            $data['status'] = 1;//默认状态为启用
            $res = $resId = $this->extendConfigModel->edit($data);

            if($res){
                cache('MUUCMF_EXT_CONFIG_DATA', null);
                //记录行为
                action_log('update_config', 'extend_config', $resId, is_login());

                return $this->success('操作成功','',url('list'));
            }else{
                return $this->error('操作失败');
            }
            
        } else {
            /* 获取数据 */
            if($id != 0){
                $info = $this->extendConfigModel->getDataById($id);
            }else{
                $info = [];
            }

            View::assign('type', get_config_type_list());
            View::assign('group', config('extend.GROUP_LIST'));
            View::assign('info', $info);
            $this->setTitle('编辑扩展配置');

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

        if (Db::name('ExtendConfig')->where('id','in', $id)->delete()) {
            cache('MUUCMF_EXT_CONFIG_DATA', null);
            //记录行为
            action_log('update_config', 'extend_config', $id, is_login());
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

}