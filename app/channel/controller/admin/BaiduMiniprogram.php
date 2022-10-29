<?php
namespace app\channel\controller\admin;

use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin as MuuAdmin;
use app\channel\model\BaiduMpConfig;

class BaiduMiniprogram extends MuuAdmin
{
    private $MiniProgramModel;
    function __construct()
    {
        parent::__construct();
        $this->MiniProgramModel = new BaiduMpConfig();
    }

    /**
     * 小程序配置
     */
    public function index()
    {
        if (request()->isPost()){
            $params = input('post.');
            $data = [
                'id' => 0,
                'shopid' => $this->shopid,
                'title' => $params['title'],
                'description' => $params['description'],
                'appid' => $params['appid'],
                'appkey' => $params['appkey'],
                'secret' => $params['secret'],
                'pay_appid' => $params['pay_appid'],
                'pay_appkey' => $params['pay_appkey'],
                'dealId' => $params['dealId'],
                'rsa_public_key' => str_replace("\n", "", $params['rsa_public_key']),
                'rsa_private_key' => str_replace("\n", "", $params['rsa_private_key'])
            ];
            $map = [
                ['shopid' ,'=' ,$this->shopid],
            ];
            $id = $this->MiniProgramModel->where($map)->value('id');
            if (!empty($id)){
                $data['id'] = $id;
            }
            $res = $this->MiniProgramModel->edit($data);
            if($res){
                return $this->success('保存成功');
            }else{
                return $this->error('保存失败');
            }
            
        }else{
            //查询分组数据
            $config = $this->MiniProgramModel->where([
                ['shopid' ,'=' ,$this->shopid],
            ])->find();

            // 设置回调地址
            $callback_url = url('channel/baidu/callback', ['shopid'=>$this->shopid], false, true);
            $config['callback'] = $callback_url;
            
            $builder = new AdminConfigBuilder();
            $builder->title('百度小程序配置')->suggest('基于第三方授权各项参数配置');

            $builder
                ->keyText('title', '小程序名称', '小程序名称.')
                ->keyTextArea('description', '小程序描述', '小程序描述')
                ->keyText('appid', 'APP ID', 'APPID是小程序的ID，请您妥善保管.')
                ->keyText('appkey', 'APP KEY', 'APPID是小程序的ID，请您妥善保管.')
                ->keyText('secret', 'App Secret', 'AppSecret是小程序的密钥，具有该账户完全的权限，请您妥善保管.')
                
                ->keyText('pay_appid', 'APP ID', '支付服务信息内 APP ID.')
                ->keyText('pay_appkey', 'APP KEY', '支付服务信息内 APP KEY.')
                ->keyText('dealId', 'dealId', '支付服务信息内 dealld.')
                ->keyTextArea('rsa_public_key', '平台公钥', '支付服务信息内 平台公钥')
                ->keyTextArea('rsa_private_key', '支付验签私钥', '私钥原始字符串，不含PEM格式前后缀')
                ->keyReadOnlyText('callback', 'URL(服务器地址)', '用于接收百度异步回调消息.')
                ->group('百度小程序配置', [
                    'title',
                    'appid',
                    'appkey',
                    'secret',
                    'description',
                ])
                ->group('支付设置', [
                    'pay_appid',
                    'pay_appkey',
                    'dealId',
                    'rsa_public_key',
                    'rsa_private_key',
                    'callback'
                ]);;
            $builder->data($config);
            $builder->buttonSubmit();
            $builder->display();
        }
    }
}