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
        return view();
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
            $list = $this->uniAccountModel->where(['group' => 'wechat_official_account','status' => 1])->order('sort','asc')->select()->toArray();
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
        $list = $this->autoReplyModel->where($where)->field('*,type as type_str,status as status_str,msg_type as msg_type_str,material_type as material_type_str')->order('sort','DESC')->page($page,20)->paginate();
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
            $data['keyword'] = input('post.keyword', '', 'text');
            $data['text'] = input('post.text', '', 'text');
            $data['media_id'] = input('post.media_id', '', 'text');
            $data['remark'] = input('post.remark', '', 'text');
            $data['sort'] = input('post.sort', 0, 'intval');
            $data['type'] = input('post.type', 1, 'intval');
            $data['msg_type'] = input('post.msg_type', 1, 'intval');
            $data['material_type'] = input('post.material_type', '', 'text');
            $data['material_json'] = input('post.material_json', '', 'text');
            $data['status'] = input('post.status', 0, 'intval');
            $data['id'] = $aId;
            //验证文本唯一性
            if (!$this->autoReplyModel->checkUnique('keyword',$data['keyword'],$aId)){
                $this->error('关键字重复');
            }
            if (!empty($data['text']) && !$this->autoReplyModel->checkUnique('text',$data['text'],$aId)){
                $this->error('内容重复');
            }
            //验证关注回复唯一性
            if ($data['type'] == 1 && !$this->autoReplyModel->checkUnique('type',1,$aId)){
                $this->error('关注消息只能添加一条');
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