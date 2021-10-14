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
namespace app\unions\controller;
use app\admin\controller\Admin as MuuAdmin;
use app\unions\model\WechatAutoReply;
use app\unions\model\WechatConfig;
use think\facade\Config;
use think\facade\View;

/**
 * 公众号管理
 * Class OfficialAccount
 * @package app\admin\controller
 */
class OfficialAccount extends MuuAdmin {
    private $wechatConfigModel;
    private $autoReplyModel;
    private $shopid = 0;
    function __construct()
    {
        parent::__construct();
        $this->wechatConfigModel = new WechatConfig();
        $this->autoReplyModel = new WechatAutoReply;
    }

    public function menu(){
        if (request()->isAjax()){
            $menu = $data = $this->wechatConfigModel->where(['shopid' => $this->shopid])->value('menu_json');
            if ($menu){
                $menu = json_decode($menu,true);
            }else{
                $menu = [];
            }
            return $this->result(200,'success',$menu);
        }
        $this->setTitle('菜单管理');
        return view();
    }
    /**
     * 保存菜单
     */
    public function saveMenu(){
        if (request()->isAjax()){
            $json = input('post.json');
            $menu = json_decode($json,true);
            $res = \app\unions\facade\OfficialAccount::createMenu($menu);
            if ($res['errcode'] != 0){
                $this->error($res['errmsg']);
            }
            $updateRes = $this->wechatConfigModel->where('shopid',$this->shopid)->save(['menu_json'=>$json]);
            if ($updateRes){
                return $this->success('更新成功','refresh');
            }
            return $this->error('更新失败');
        }
    }
    /**
     * 存储配置
     */
    public function index()
    {
        if (request()->isPost()) {
            $config = input('post.');
            $config['shopid'] = $this->shopid;
            $res = $this->wechatConfigModel->edit($config);
            if ($res){
                return $this->success('保存成功',$config, 'refresh');
            }
            return $this->error('网络异常，请稍后再试');

        }else{
            //查询微信平台配置
            $data = $this->wechatConfigModel->getWechatConfigByShopId($this->shopid)->toArray();
            if (!$data){
                $data['id'] = 0;
                $data['url'] = \app\unions\facade\OfficialAccount::callbackUrl();
            }
            View::assign('data', $data);
            return view();
        }
    }

    /**
     * 自动回复列表
     */
    public function autoReply()
    {
        $this->setTitle('自动回复');
        $params = input('get.');
        $where = [
            ['status','>=',0],
        ];
        if (isset($params['keyword']) && !empty($params['keyword'])) $where[] = ['keyword','like','%' . $params['keyword'] . '%'];
        $page = max(1,isset($params['page']) ?? $params['page']);
        $list = $this->autoReplyModel->where($where)->field('*,type as type_str,status as status_str,msg_type as msg_type_str')->order('sort','DESC')->page($page,20)->paginate();
        // 获取分页显示
        $page = $list->render();
        unset($val);

        //显示页面
        View::assign('list', $list);
        View::assign('page', $page);

        return View::fetch();
    }

    /**
     * 添加、更新自动回复
     * @return \think\Response|void
     */
    public function editAutoReply()
    {
        $aId = input('param.id', 0, 'intval');
        if (request()->isPost()) {
            $msg_type = input('post.msg_type', 1, 'intval');
            $data['keyword'] = input('post.keyword', '', 'text');
            $data['text'] = input('post.text', '', 'text');
            $data['media_id'] = input('post.media_id', '', 'text');
            $data['remark'] = input('post.remark', '', 'text');
            $data['sort'] = input('post.sort', 0, 'intval');
            $data['type'] = input('post.type', 1, 'intval');
            $data['material_json'] = input('post.material_json', '', 'text');
            $data['status'] = input('post.status', 0, 'intval');
            $data['id'] = $aId;
            if ($msg_type == 1){
                $data['msg_type'] = 'text';
            }else{
                $data['msg_type'] = input('post.material_type');
            }
            //验证文本唯一性
            if (!empty($data['text']) && !$this->autoReplyModel->checkUnique('text',$data['text'],$aId)){
                $this->error('内容重复');
            }
            $res = $this->autoReplyModel->edit($data);
            if($res){
                return $this->success(($aId == 0 ? '新增' : '编辑') . '成功', '', url('unions/OfficialAccount/autoReply'));
            }else{
                return $this->error('提交失败');
            }
        }else {
            $data = ['id' => $aId];
            if ($aId > 0){
                $data = $this->autoReplyModel->find(['id' => input('id')]);
            }
            View::assign([
                'data' => $data
            ]);
            return \view();
        }
    }

    /**
     * 修改自动回复状态
     */
    public function autoReplyStatus(int $status = 0)
    {
        $ids = array_unique((array)input('ids/a', 0));
        $ids = is_array($ids) ? implode(',', $ids) : $ids;

        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }

        $map = ['id' => ['in', $ids]];

        switch (strtolower($status)) {
            case 0:
                return $this->forbid('wechat_auto_reply', $map);
                break;
            case 1:
                return $this->resume('wechat_auto_reply', $map);
                break;
            case -1:
                return $this->delete('wechat_auto_reply', $map);
                break;
            default:
                return $this->error('参数错误');
        }
    }

    /**
     * 素材列表
     */
    public function material(){
        if (request()->isAjax()){
            $params = input('post.');
            $page = ($params['page'] - 1) * 20;
            $data = \app\unions\facade\OfficialAccount::getMaterialList($params['type'], $page,20);
            if (isset($data['item'])){
                return  $this->success('success',$data);
            }elseif (isset($data['errmsg'])){
                return $this->error($data['errmsg']);
            }
            return $this->error('请检查公众号配置');
        }
    }
}