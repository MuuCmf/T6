<?php
namespace app\channel\controller\admin;

use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin as MuuAdmin;
use app\common\model\Module;
use app\channel\logic\TemplateMessage;
use app\channel\model\WechatMpConfig;
use think\facade\View;

class WechatMiniProgram extends MuuAdmin{
    private $MiniProgramModel;
    function __construct()
    {
        parent::__construct();
        $this->MiniProgramModel = new WechatMpConfig();
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