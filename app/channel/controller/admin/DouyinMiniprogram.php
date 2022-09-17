<?php
namespace app\channel\controller\admin;

use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin as MuuAdmin;
use app\channel\logic\TemplateMessage;
use app\channel\model\DouyinMpConfig;
use think\facade\View;

class DouyinMiniProgram extends MuuAdmin{
    private $MiniProgramModel;
    function __construct()
    {
        parent::__construct();
        $this->MiniProgramModel = new DouyinMpConfig();
    }

    /**
     * 小程序配置
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
                'secret' => $params['secret'],
                'token' => $params['token'],
                'salt' => $params['salt']
            ];
            $map = [
                ['shopid' ,'=' ,$this->shopid],
            ];
            $id = $this->MiniProgramModel->where($map)->value('id');
            if ($id){
                $data['id'] = $id;
            }
            $this->MiniProgramModel->edit($data);
            return $this->success('保存成功');
        }else{
            //查询分组数据
            //查询数据
            $config = $this->MiniProgramModel->where([
                ['shopid' ,'=' ,$this->shopid],
            ])->find();

            // 设置回调地址
            $callback_url = url('channel/douyin/callback', ['shopid'=>$this->shopid], false, true);
            $config['callback'] = $callback_url;
            
            $builder = new AdminConfigBuilder();
            $builder->title('抖音小程序配置')->suggest('基于第三方授权各项参数配置');

            $builder
                ->keyText('title', '小程序名称', '小程序名称.')
                ->keyText('appid', 'APPID', 'APPID是小程序的ID，请您妥善保管.')
                ->keyText('secret', 'AppSecret', 'AppSecret是小程序的密钥，具有该账户完全的权限，请您妥善保管.')
                ->keyTextArea('description', '小程序描述', '小程序描述')
                ->keyText('token', 'Token', 'Token（令牌）.')
                ->keyText('salt', 'SALT', 'SALT')
                ->keyReadOnly('callback', 'URL(服务器地址)', '用于接收抖音异步通知消息.')
                ->group('抖音小程序配置', [
                    'title',
                    'appid',
                    'secret',
                    'description',
                ])
                ->group('支付设置', [
                    'token',
                    'salt',
                    'callback'
                ]);;
            $builder->data($config);
            $builder->buttonSubmit();
            $builder->display();
        }
    }

    /**
     * @title 模板消息通知
     * @return \think\response\View
     */
    public function templateMessage(){
        if (request()->isAjax()){
            $params = request()->post();
            $data = [
                'switch'      => $params['switch'],
                'to'          => $params['to'],
                'manager_uid' => $params['manager_uid'],
                'tmplmsg'     => $params['tmplmsg']
            ];
            $data = json_encode($data);
            $result = $this->MiniProgramModel->where('shopid',$this->shopid)->save(['tmplmsg' => $data]);
            if ($result){
                return $this->success('保存成功');
            }
            return $this->error('保存失败，请稍后再试');
        }
        $type = 'weixin_app';//当前模板消息类型
        $TemplateMessageLogic = new TemplateMessage();
        $detail = $this->MiniProgramModel->where('shopid',$this->shopid)->value('tmplmsg');
        $detail = $TemplateMessageLogic->formatData($detail);//格式化原始数据
        View::assign([
            'type' => $type,
            'element' => $TemplateMessageLogic->oauth_type[$type],
            'data' => $detail
        ]);
        return \view('admin/common/template_message');
    }
}