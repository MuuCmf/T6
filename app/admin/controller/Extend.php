<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\facade\Cache;
use app\admin\builder\AdminConfigBuilder;
use app\common\model\ExtendConfig as MuuExtendConfigModel;
use app\admin\validate\Common as CommonValidate;
use think\exception\ValidateException;

/**
 * 后台配置控制器
 */
class Extend extends Admin
{
    protected $moduleModel;
    protected $extendConfigModel;

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
            $list = $this->extendConfigModel->lists();

            $builder = new AdminConfigBuilder();
            $builder->title('短信配置')->suggest('基于第三方短信发送各项参数配置');
    
            // 基础配置
            $sms_send_driver_opt = $this->extendConfigModel->getExtraByName('SMS_SEND_DRIVER');
            
            $builder->keySelect('SMS_SEND_DRIVER', '选择平台', '请选择短信发送第三方平台' , $sms_send_driver_opt);
            $builder->keyInteger('SMS_RESEND', '验证码有效期', '单位：秒');
            $builder->group('基础配置', ['SMS_SEND_DRIVER', 'SMS_RESEND']);
            
            // 阿里云短信参数配置
            $builder
                ->keyText('SMS_ALIYUN_ACCESSKEYID', 'AccessKeyID', 'Access Key ID是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('SMS_ALIYUN_ACCESSKEYSECRET', 'AccessKeySecret', 'Access Key Secret是您访问阿里云API的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyText('SMS_ALIYUN_REGION', 'Region', '区域信息，格式 如：cn-beijing.')
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
                ->keyText('SMS_TENCENT_REGION', 'Region', '区域信息，格式 如：ap-beijing.')
                ->keyText('SMS_TENCENT_APPID', 'AppID', 'SDK AppID是短信应用的唯一标识，调用短信API接口时，需要提供该参数.')
                ->keyText('SMS_TENCENT_SIGN', '短信签名', '请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`.')
                ->keyText('SMS_TENCENT_TEMPLATEID', '短信模板', '短信模板ID，应严格按"模板ID"填写')
                ->group('腾讯云短信', [
                    'SMS_TENCENT_SECRETID',
                    'SMS_TENCENT_SECRETKEY',
                    'SMS_TENCENT_REGION',
                    'SMS_TENCENT_APPID', 
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
                ->keyText('WX_PAY_CERT_SERIAL', 'API证书序列号', '商户API证书序列号.')
                ->keySingleFile('WX_PAY_CERT', 'Cert证书','Cert证书上传', ['enforce' => 'local'])
                ->keySingleFile('WX_PAY_KEY', 'Key证书','Key证书上传', ['enforce' => 'local'])
                ->keyText('WX_PAY_WITHDRAW_PLATFORM_SERIAL', '支付平台证书序列号', '当使用商家转账到零钱接口时需填写.')
                ->group('微信支付', [
                    'WX_PAY_MCH_ID',
                    'WX_PAY_KEY_SECRET',
                    'WX_PAY_CERT_SERIAL',
                    'WX_PAY_CERT',
                    'WX_PAY_KEY',
                    'WX_PAY_WITHDRAW_PLATFORM_SERIAL'
                ]);

            // 支付宝支付参数配置

            // 提现参数配置
            $opt = [0 => '关闭' ,1 => '开启'];
            $builder
                ->keySelect('WITHDRAW_STATUS', '提现开关', '如有特殊情况，可暂时关闭提现',$opt)
                ->keyRadio('WX_PAY_WITHDRAW_API', '提现方式接口','请选择您申请的提现方式接口', ['v2' => '企业付款到零钱', 'v3' => '商家转账'])
                ->keyText('WITHDRAW_TRANSFER_SCENE_ID', '商家转账场景ID', '当使用商家转账接口时需填写，当前仅支持1000（现金营销）、1005（佣金报酬）')
                ->keyText('WITHDRAW_TAX_RATE', '提现费率', '单位：千分比')
                ->keyText('WITHDRAW_DAY_NUM', '每日可提现次数', '日最多可提现多少次')
                ->keyText('WITHDRAW_MIN_PRICE', '单次最小提现金额', '单位：元')
                ->keyText('WITHDRAW_MAX_PRICE', '单次最大提现金额', '单位：元')
                ->group('提现配置', [
                    'WITHDRAW_STATUS',
                    'WX_PAY_WITHDRAW_API',
                    'WITHDRAW_TRANSFER_SCENE_ID',
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
            $builder->title('存储配置')->suggest('基于第三方云存储各项参数配置');

            // 基础配置
            $picture_upload_driver_opt = $this->extendConfigModel->getExtraByName('PICTURE_UPLOAD_DRIVER');
            $file_upload_driver_opt = $this->extendConfigModel->getExtraByName('FILE_UPLOAD_DRIVER');
            
            $builder
                ->keySelect('PICTURE_UPLOAD_DRIVER', '图片', '图片上传驱动', $picture_upload_driver_opt)
                ->keySelect('FILE_UPLOAD_DRIVER', '文件', '文件上传驱动', $file_upload_driver_opt)
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
                ->keyText('COS_TENCENT_SECRETID', 'SecretID', 'SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('COS_TENCENT_SECRETKEY', 'SecretKEY', 'SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('COS_TENCENT_BUCKET', 'Bucket', 'Bucket名称.')
                ->keyText('COS_TENCENT_REGION', 'Region', 'Bucket所在区域，格式 如：ap-beijing.')
                ->keyText('COS_TENCENT_BUCKET_DOMAIN', 'Bucket域名', '腾讯云支持用户自定义访问域名。注：url开头加http://或https://结尾不加 ‘/’例：http://abc.com.')
                ->group('腾讯云COS', [
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
            $list = $this->extendConfigModel->lists();

            $builder = new AdminConfigBuilder();
            $builder->title('音视频点播配置')->suggest('基于第三方音视频点播各项参数配置');
            
            // 基础配置
            $vod_upload_driver_opt = $this->extendConfigModel->getExtraByName('VOD_UPLOAD_DRIVER');
            
            $builder
                ->keySelect('VOD_UPLOAD_DRIVER', '音视频点播', '音视频点播上传驱动', $vod_upload_driver_opt)
                ->group('基础配置', [
                    'VOD_UPLOAD_DRIVER'
                ]);

            // 腾讯云VOD参数配置
            $builder
                ->keyText('VOD_TENCENT_SECRETID', 'SecretID', 'SecretID 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('VOD_TENCENT_SECRETKEY', 'SecretKEY', 'SecretKEY 是您项目的安全密钥，具有该账户完全的权限，请妥善保管.')
                ->keyText('VOD_TENCENT_SUBAPPID', 'SubAppId', 'SubAppId 是您云点播平台子应用ID，请妥善保管.')
                ->keyRadio('VOD_TENCENT_PROCEDURE', '媒体处理任务流', '启用后会触发云点播媒体处理任务流.', [0 => '不启用', 1 => '启用'])
                ->keyText('VOD_TENCENT_PROCEDURE_NAME', '任务流名称', '请在云点播媒体处理设置->任务流设置内获取任务流名称，支持仅SimpleAesEncryptPreset、WidevineFairPlayPreset.')
                ->keyRadio('VOD_TENCENT_KEY_SWITCH', 'key防盗链开关', 'key防盗链开关', [0 => '不启用', 1 => '启用'])
                ->keyText('VOD_TENCENT_KEY_VALUE', '防盗链 Key', '必须由大小写字母（a - Z）或者数字（0 - 9）组成，长度在8 - 20个字符之间.')
                ->keyText('VOD_TENCENT_PLAYER_KEY', '播放秘钥', '请在分发播放设置->默认分发配置信息内播放秘钥，仅启用KEY防盗链后有效.')
                ->keyRadio('VOD_TENCENT_LICENSE_TYPE', '播放器license版本', '请勾选创建的播放器license版本.', [0 => '基础版', 1 => '高级版'])
                ->keyText('VOD_TENCENT_LICENSE_URL', '播放器licenseUrl', 'WEB播放器SDK License 地址.')
                ->group('腾讯云点播', [
                    'VOD_TENCENT_SECRETID',
                    'VOD_TENCENT_SECRETKEY',
                    'VOD_TENCENT_SUBAPPID',
                    'VOD_TENCENT_PROCEDURE',
                    'VOD_TENCENT_PROCEDURE_NAME',
                    'VOD_TENCENT_KEY_SWITCH',
                    'VOD_TENCENT_KEY_VALUE',
                    'VOD_TENCENT_PLAYER_KEY',
                    'VOD_TENCENT_LICENSE_TYPE',
                    'VOD_TENCENT_LICENSE_URL'
                ]);

            $builder->data($list);
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    /**
     * 获取扩展配置分组列表
     */
    public function groupList()
    {
        // 配置分组
        $group = config('extend.GROUP_LIST');
        return $this->success('success', $group);
    }

    /**
     * 扩展配置管理
     */
    public function list()
    {
        // 加载方式 all 全量查询  page 分页查询
        $load = input('load', 'page', 'text');
        // 配置分组 多个分组用,号隔开
        $group = input('group', '', 'text');
        $keyword = input('keyword', '', 'trim');
        View::assign('keyword', $keyword);
        $rows = input('rows', 20, 'intval');
        View::assign('rows', $rows);

        /* 查询条件初始化 */
        $map = [];
        $map[] = ['status', '=', 1];
        if (!empty($group)) {
            // 如果$group为,分割的字符串，转换为数组
            $group = explode(',', $group);
            // 筛选出$group中包含的配置分组
            $group = array_intersect($group, array_keys(config('extend.GROUP_LIST')));
            // 筛选出配置项
            $map[] = ['group', 'in', $group];
        }
        if (!empty($keyword)) {
            $map[] = [
                'OR',
                ['name', 'like', '%' . $keyword . '%'],
                ['title', 'like', '%' . $keyword . '%']
            ];
        }

        if ($load == 'page') {
            // 分页查询
            $list = $this->extendConfigModel->getListByPage($map, 'sort asc,id desc', '*', $rows);
            $pager = $list->render();
            $list = $list->toArray();
            foreach ($list['data'] as $key => $item) {
                $list['data'][$key]['type_name'] = get_config_type($item['type']);
                $list['data'][$key]['group_name'] = get_extend_config_group($item['group']);
                // pic类型生成缩微图组
                if ($item['type'] == 'pic' && !empty($item['value'])) {
                    $list['data'][$key]['thumb'] = thumb_group($item['value']);
                }
            }
        } else {
            // 全量查询
            $list = $this->extendConfigModel->where($map)->field('id,name,title,extra,value,group,remark,type')->order('sort asc')->select()->toArray();
            $pager = '';
            foreach ($list as $key => $item) {
                $list[$key]['type_name'] = get_config_type($item['type']);
                $list[$key]['group_name'] = get_extend_config_group($item['group']);
                // pic类型生成缩微图组
                if ($item['type'] == 'pic' && !empty($item['value'])) {
                    $list['data'][$key]['thumb'] = thumb_group($item['value']);
                }
            }
        }

        if( request()->isAjax()){
            return $this->success('success', $list);
        }
        
        View::assign('list', $list);
        View::assign('pager', $pager);

        $this->setTitle('配置管理');

        return View::fetch();
    }

    /**
     * 编辑系统配置
     */
    public function edit()
    {
        $id = input('id', 0, 'intval');

        if (request()->isPost()) {
            $data = request()->param();
            //验证器
            try {
                validate(CommonValidate::class)->scene('config')->check($data);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
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
            $info = [
                'type' => '',
                'group' => '',
                'name' => '',
                'title' => '',
                'value' => '',
                'extra' => '',
            ];
            if(!empty($id)){
                $info = $this->extendConfigModel->getDataById($id);
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