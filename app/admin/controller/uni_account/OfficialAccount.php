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
use app\common\model\WechatAutoReply;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\View;

/**
 * 公众号管理
 * Class OfficialAccount
 * @package app\admin\controller
 */
class OfficialAccount extends Admin {
    private  $uniAccountModel;
    private  $autoReplyModel;
    function __construct()
    {
        parent::__construct();
        $this->uniAccountModel = new UniAccount();
        $this->autoReplyModel = new WechatAutoReply;
    }

    public function menu(){
        if (request()->isAjax()){
            $menu = Config::get('uni_account.MP_MENU');
            $res = json_decode($menu,true);
            return $this->result(200,'success',$res);
        }
        $this->setTitle('菜单管理');
        return view();
    }

    /**
     * 同步远端菜单
     */
    public function syncMenu(){
        if (request()->isAjax()){
            $menu = \app\common\service\wechat\facade\OfficialAccount::getMenu();
            $menu = $menu['menu']['button'];
            if ($menu){
                return $this->success('同步成功',$menu);
            }
            return $this->error('同步失败');
        }
    }

    /**
     * 保存菜单
     */
    public function saveMenu(){
        if (request()->isAjax()){
            $json = input('post.json');
            $menu = json_decode($json,true);
            $res = \app\common\service\wechat\facade\OfficialAccount::createMenu($menu);
            if ($res['errcode'] != 0){
                $this->error($res['errmsg']);
            }
            $updateRes = (new UniAccount())->where('name','MP_MENU')->save(['value'=>$json]);
            if ($updateRes){
                // 清理缓存
                Config::set([],'uni_account');
                cache('MUUCMF_UNI_ACCOUNT_CONFIG_DATA', null);
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
            $config = input('post.config');
            if ($config && is_array($config)) {
                foreach ($config as $name => $value) {
                    $map = ['name' => $name];
                    $this->uniAccountModel->where($map)->update(['value' => $value]);
                }
            }
            // 清理缓存
            cache('MUUCMF_UNI_ACCOUNT_CONFIG_DATA', null);

            return $this->success('保存成功',$config, 'refresh');

        }else{
            //查询微信平台配置
            $list = $this->uniAccountModel->where(['group' => 'wechat_official_account','status' => 1])->order('sort','DESC')->order('id','DESC')->select()->toArray();
            View::assign('list', $list);
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
                return $this->success(($aId == 0 ? '新增' : '编辑') . '成功', '', url('admin/uni_account.OfficialAccount/autoReply'));
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
            $data = \app\common\service\wechat\facade\OfficialAccount::getMaterialList($params['type'], $page,20);
            if (isset($data['item'])){
                return  $this->success('success',$data);
            }elseif (isset($data['errmsg'])){
                return $this->error($data['errmsg']);
            }
            return $this->error('请检查公众号配置');
        }
    }
}