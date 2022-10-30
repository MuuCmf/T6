<?php
namespace app\channel\controller\admin;

use app\admin\builder\AdminConfigBuilder;
use app\admin\controller\Admin as MuuAdmin;
use app\channel\logic\TemplateMessage;
use app\channel\model\DouyinMpConfig;
use app\channel\model\DouyinMpSettle as DouyinMpSettleModel;
use app\channel\service\bytedance\DouyinMp as DouyinMpService;
use app\common\model\Orders as OrdersModel;
use app\common\logic\Orders as OrdersLogic;
use think\facade\View;

class DouyinMiniprogram extends MuuAdmin
{
    private $MiniProgramModel;
    protected $DouyinMpSettleModel;
    function __construct()
    {
        parent::__construct();
        $this->MiniProgramModel = new DouyinMpConfig();
        $this->DouyinMpSettleModel = new DouyinMpSettleModel();
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
                'weixin_merchant_uid' => $params['weixin_merchant_uid'],
                'alipay_merchant_uid' => $params['alipay_merchant_uid'],
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
                ->keyText('weixin_merchant_uid', '微信支付商户号', '进件完成返回的微信支付商户号.')
                ->keyText('alipay_merchant_uid', '支付宝商户号', '进件完成返回的支付宝商户号.')
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
                    'weixin_merchant_uid',
                    'alipay_merchant_uid',
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
     * 抖音订单结算列表
     */
    public function settle()
    {
        $keyword = input('keyword', '', 'text');
        View::assign('keyword', $keyword);
        $status = input('status') == null?'all':input('status');
        View::assign('status', $status);
        $rows = input('rows', 20, 'intval');

        // 获取查询条件
        $map = [];
        if($status == 'all'){
            $map[] = ['status', 'in', [0,1]];
        }else{
            $map[] = ['status', '=', $status];
        }
        
        // 获取列表
        $lists = $this->DouyinMpSettleModel->getListByPage($map, 'id DESC', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->DouyinMpSettleModel->handle($val);
        }
        unset($val);

        // ajax请求返回数据
        if(request()->isAjax()){
            return $this->success('success', $lists);
        }
        View::assign('pager',$pager);
        View::assign('lists',$lists);

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->setTitle('结算列表');
        // 输出模板
        return View::fetch();
    }

    /**
     * 未结算订单列表
     */
    public function orders()
    {
        $OrdersModel = new OrdersModel();
        $OrdersLogic = new OrdersLogic();

        $keyword = input('keyword', '', 'text');
        View::assign('keyword', $keyword);
        $status = input('status') == null?'all':input('status');
        View::assign('status', $status);
        $rows = input('rows', 20, 'intval');

        // 查询条件
        $map = [
            'shopid' => $this->shopid,
            'paid' => 1,
            'channel' => 'douyin_mp',
            'settle' => 0
        ];

        // 获取列表
        $lists = $OrdersModel->getListByPage($map, 'id DESC', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $OrdersLogic->formatData($val);
            // 是否允许结算
            $val['can_settle'] = 0;
            if($val['paid_time'] + (86400 *7) < time()){
                $val['can_settle'] = 1;
            }

            // 查询是否有分账数据
            $val['has_settle'] = 0;
            $has_settle = $this->DouyinMpSettleModel->where('order_no', $val['order_no'])->find();
            if(!empty($has_settle)){
                $val['has_settle'] = 1;
            }
        }
        unset($val);

        // ajax请求返回数据
        if(request()->isAjax()){
            return $this->success('success', $lists);
        }
        View::assign('pager',$pager);
        View::assign('lists',$lists);

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->setTitle('结算列表');
        // 输出模板
        return View::fetch();
    }

    /**
     * 手动触发结算分账
     */
    public function manualSettle()
    {
        $order_no = input('order_no', '', 'text');
        $OrdersModel = new OrdersModel();
        if(!empty($order_no)){
            $order_info = $OrdersModel->where('order_no', $order_no)->find();
            $settle_no = build_order_no();
            // 查询是否已有该订单分账数据
            $has_settle = $this->DouyinMpSettleModel->where('order_no', '=', $order_no)->find();

            // 预写入分账表
            $settle_id = 0;
            if(empty($has_settle)){
                $settle_id = $this->DouyinMpSettleModel->edit([
                    'shopid' => $this->shopid,
                    'settle_no' => $settle_no,
                    'order_no' => $order_no,
                    'price' => $order_info['paid_fee'],
                    'status' => 0
                ]);
            }
            
            // 请求结算分账
            $result = (new DouyinMpService)->settle($settle_no, $order_no);
            
            // "err_no" => 0
            // "err_tips" => "success"
            // "settle_no" => "7147090344914766092"
            if($result['err_no'] == 0){
                // 更新结算分账表
                $this->DouyinMpSettleModel->edit([
                    'id' => $settle_id,
                    'shopid' => $this->shopid,
                    'settle_no' => $settle_no,
                    'order_no' => $order_no,
                    'price' => $order_info['paid_fee'],
                    'douyin_settle_no' => $result['settle_no'],
                    'status' => 0
                ]);
                // 更改分账状态
                $OrdersModel->edit([
                    'id' => $order_info['id'],
                    'settle' => 0
                ]);

                return $this->success('手动结算发送成功');
            }else{
                return $this->error($result['err_tips']);
            }
        }
        return $this->error('参数错误');
    }

    /**
     * 结算及分账结果查询
     */
    public function manualSettleQuery()
    {
        $order_no = input('order_no', '', 'text');
        // 查询订单数据
        $OrdersModel = new OrdersModel();
        $order_info = $OrdersModel->where('order_no', $order_no)->find();
        // 查询分账表数据
        $has_settle = $this->DouyinMpSettleModel->where('order_no', '=', $order_no)->find();
        $settle_no = $has_settle['settle_no'];
       
        $result = (new DouyinMpService)->settleQuery($settle_no);
        if($result['err_no'] == 0){
            // 更新结算分账表
            $this->DouyinMpSettleModel->edit([
                'id' => $has_settle['id'],
                'douyin_settle_no' => $result['settle_info']['settle_no'],
                'status' => 1
            ]);
            // 更改分账状态
            $OrdersModel->edit([
                'id' => $order_info['id'],
                'settle' => 1
            ]);

            return $this->success('校验成功');
        }else{
            return $this->error($result['err_tips']);
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