<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\facade\Cache;
use app\admin\builder\AdminConfigBuilder;
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
            $config = input('post.');
            //dump($config);exit;
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    Db::name('ExtendConfig')->where($map)->save(['value' => $value]);
                }
            }
            // 清理缓存
            Cache::delete(request()->host() . '_MUUCMF_EXT_CONFIG_DATA');

            return $this->success('保存成功',$config, 'refresh');

        }else{

            $list = Db::name("ExtendConfig")->where(['status' => 1])->field('id,name,title,extra,value,group,remark,type')->order('sort asc')->select()->toArray();
            $list = $this->extendConfigModel->lists();
//            dump($list);die();
            $builder = new AdminConfigBuilder();
            $builder->title('短信配置')->suggest('基于第三方短信发送各项参数配置');
    
            // 基础配置
            $opt = ['aliyun' => '阿里云', 'tencent' => '腾讯云'];
            $builder->keySelect('SMS_SEND_DRIVER', '选择平台', '请选择短信发送第三方平台' , $opt);
            $builder->keyInteger('SMS_RESEND', '验证码有效期', '单位：秒');
            $builder->group('基础配置', ['SMS_SEND_DRIVER', 'SMS_RESEND']);
            
            // 阿里云短信参数配置
            $builder
                ->keyText('SMS_ALIYUN_ACCESSKEYID', 'AccessKeyID', 'Access Key ID是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('SMS_ALIYUN_ACCESSKEYSECRET', 'AccessKeySecret', 'Access Key Secret是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('SMS_ALIYUN_REGION', 'Region', 'Bucket所在区域，格式 如：cn-beijing.')
                ->keyText('SMS_ALIYUN_SIGN', '短信签名', '短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign.')
                ->keyText('SMS_ALIYUN_TEMPLATEID', '短信模板', '短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template.')
                ->group('阿里云短信', [
                    'SMS_ALIYUN_ACCESSKEYID', 
                    'SMS_ALIYUN_ACCESSKEYSECRET',
                    'SMS_ALIYUN_REGION',
                    'SMS_ALIYUN_SIGN',
                    'SMS_ALIYUN_TEMPLATEID'
                ]);

            // 腾讯云短信参数配置
            $builder
                ->keyText('SMS_TENCENT_SECRETID', 'SecretID', 'SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('SMS_TENCENT_SECRETKEY', 'SecretKEY', 'SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('SMS_TENCENT_REGION', 'Region', 'Bucket所在区域，格式 如：ap-beijing.')
                ->keyText('SMS_TENCENT_APPID', 'AppID', 'SDK AppID是短信应用的唯一标识，调用短信API接口时，需要提供该参数.')
                //->keyText('SMS_TENCENT_APPKEY', 'App KEY', 'App Key是用来校验短信发送合法性的密码，与SDK AppID对应，需要业务方高度保密，切勿把密码存储在客户端.')
                ->keyText('SMS_TENCENT_SIGN', '短信签名', '请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`.')
                ->keyText('SMS_TENCENT_TEMPLATEID', '短信模板', '短信模板ID，应严格按"模板ID"填写')
                ->group('腾讯云短信', [
                    'SMS_TENCENT_SECRETID',
                    'SMS_TENCENT_SECRETKEY',
                    'SMS_TENCENT_REGION',
                    'SMS_TENCENT_APPID', 
                    //'SMS_TENCENT_APPKEY',
                    'SMS_TENCENT_SIGN',
                    'SMS_TENCENT_TEMPLATEID'
                ]);

            $builder->data($list);
            $builder->buttonSubmit();
            $builder->display();
        }

    }

    /**
     * 支付参数配置
     */
    public function payment() {

        if (request()->isPost()) {
            $config = input('post.');
            //dump($config);exit;
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    Db::name('ExtendConfig')->where($map)->save(['value' => $value]);
                }
            }
            // 清理缓存
            Cache::delete(request()->host() . '_MUUCMF_EXT_CONFIG_DATA');

            return $this->success('保存成功',$config, 'refresh');

        }else{
            $list = $this->extendConfigModel->lists();
            $builder = new AdminConfigBuilder();
            $builder->title('支付配置')->suggest('基于第三方支付各项参数配置');
            // 微信支付参数配置
            $builder
                ->keyText('WX_PAY_MCH_ID', 'MchID', 'Mch ID是您微信商户的商 户ID，请您妥善保管.')
                ->keyText('WX_PAY_KEY_SECRET', 'KeySecret', 'Key Secret是您微信商户的API密钥，请您妥善保管.')
                ->keySingleFile('WX_PAY_CERT', 'Cert证书','Cert证书上传', ['enforce' => 'local'])
                ->keySingleFile('WX_PAY_KEY', 'Key证书','Key证书上传', ['enforce' => 'local'])
                ->group('微信', [
                    'WX_PAY_MCH_ID',
                    'WX_PAY_KEY_SECRET',
                    'WX_PAY_CERT',
                    'WX_PAY_KEY'
                ]);

            // 支付宝支付参数配置

            // 提现参数配置
            $opt = [0 => '关闭' ,1 => '开启'];
            $builder
                ->keySelect('WITHDRAW_STATUS', '提现开关', '如有特殊情况，可暂时关闭提现',$opt)
                ->keyText('WITHDRAW_TAX_RATE', '提现税率', '默认千分之五（千分比）')
                ->keyText('WITHDRAW_DAY_NUM', '每日可提现次数', '一天最多可提现多少次')
                ->keyText('WITHDRAW_MIN_PRICE', '单次最小提现金额', '一次最少提现金额')
                ->keyText('WITHDRAW_MAX_PRICE', '单次最大提现金额', '一次最大提现金额')
                ->group('提现配置', [
                    'WITHDRAW_STATUS',
                    'WITHDRAW_TAX_RATE',
                    'WITHDRAW_DAY_NUM',
                    'WITHDRAW_MIN_PRICE',
                    'WITHDRAW_MAX_PRICE',
                ]);

            $builder->data($list);
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
            // 清理缓存
            Cache::delete(request()->host() . '_MUUCMF_EXT_CONFIG_DATA');
    
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
     * 云点播配置管理
     */
    public function vod()
    {
        if (request()->isPost()) {
            $config = input('post.');
            
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    Db::name('ExtendConfig')->where($map)->save(['value' => $value]);
                }
            }
            // 清理缓存
            Cache::delete(request()->host() . '_MUUCMF_EXT_CONFIG_DATA');
    
            return $this->success('保存成功',$config, 'refresh');

        }else{

            $list = Db::name("ExtendConfig")->where(['status' => 1])->field('id,name,title,extra,value,group,remark,type')->order('sort asc')->select()->toArray();
            $list = $this->extendConfigModel->lists();

            $builder = new AdminConfigBuilder();
            $builder->title('云点播配置')->suggest('基于第三方云点播各项参数配置');
            
            // 基础配置
            $opt = ['disable' => '不启用', 'tencent' => '腾讯云'];
            $builder
                ->keySelect('VOD_UPLOAD_DRIVER', '云点播', '云点播上传驱动', $opt)
                ->group('基础配置', [
                    'VOD_UPLOAD_DRIVER'
                ]);
            
            // // 阿里云OSS参数配置
            // $builder
            //     ->keyText('OSS_ALIYUN_ACCESSKEYID', 'AccessKeyID', 'Access Key ID是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
            //     ->keyText('OSS_ALIYUN_ACCESSKEYSECRET', 'AccessKeySecret', 'Access Key Secret是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
            //     ->keyText('OSS_ALIYUN_ENDPOINT', 'Endpoint', '如：oss-cn-beijing.aliyuncs.com.')
            //     ->keyText('OSS_ALIYUN_BUCKET', 'Bucket', 'Bucket.')
            //     ->keyText('OSS_ALIYUN_BUCKET_DOMAIN', 'Bucket域名', 'Bucket域名.')
            //     ->group('阿里云OSS', [
            //         'OSS_ALIYUN_ACCESSKEYID', 
            //         'OSS_ALIYUN_ACCESSKEYSECRET',
            //         'OSS_ALIYUN_ENDPOINT',
            //         'OSS_ALIYUN_BUCKET',
            //         'OSS_ALIYUN_BUCKET_DOMAIN'
            //     ]);

            // 腾讯云VOD参数配置
            $builder
                ->keyText('VOD_TENCENT_SECRETID', 'SecretID', 'SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('VOD_TENCENT_SECRETKEY', 'SecretKEY', 'SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('VOD_TENCENT_SUBAPPID', 'SubAppId', 'SubAppId 是您云点播平台子应用ID，请妥善保管.')
                ->group('腾讯云COS', [
                    'VOD_TENCENT_SECRETID',
                    'VOD_TENCENT_SECRETKEY',
                    'VOD_TENCENT_SUBAPPID',
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
                Cache::delete(request()->host() . '_MUUCMF_EXT_CONFIG_DATA');
                //记录行为
                action_log('update_config', 'extend_config', $resId, is_login());

                return $this->success('操作成功','',url('list')->build());
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
            Cache::delete(request()->host() . '_MUUCMF_EXT_CONFIG_DATA');
            //记录行为
            action_log('update_config', 'extend_config', $id, is_login());
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

}