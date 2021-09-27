<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: UniAccount.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/27
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller\uni_account;
use app\admin\controller\Admin;
use think\facade\Db;
use think\facade\View;

class UniAccount extends Admin{
    private  $uniAccountModel;
    function __construct()
    {
        parent::__construct();
        $this->uniAccountModel = new \app\common\model\UniAccount();
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

        list($list,$page) = $this->commonLists('UniAccount', $map, 'sort,id');
        $list = $list->toArray()['data'];
        View::assign([
            'group' => config('uni_account.GROUP_LIST'),
            'group_id' => input('get.group', 0),
            'list' => $list,
            'page' => $page
        ]);
        return View::fetch();
    }

    /**
     * 编辑系统配置
     */
    public function edit($id = 0)
    {
        if (request()->isPost()) {
            $data = input('post.');
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
            if (!empty($data['id'])){
                $res = $resId = $this->uniAccountModel->update($data);
            }else{
                $res = $resId = $this->uniAccountModel->insertGetId($data);
            }

            if($res){
                cache('MUUCMF_UNI_ACCOUNT_CONFIG_DATA', null);
                //记录行为
                action_log('update_config', 'uni_account_config', $resId, is_login());
                return $this->success('操作成功','',url('list')->build());
            }else{
                return $this->error('操作失败');
            }

        } else {
            /* 获取数据 */
            if($id != 0){
                $info = $this->uniAccountModel->find($id);
            }else{
                $info = [];
            }

            View::assign('type', get_config_type_list());
            View::assign('group', config('uni_account.GROUP_LIST'));
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

        if (Db::name('UniAccount')->where('id','in', $id)->delete()) {
            cache('MUUCMF_UNI_ACCOUNT_CONFIG_DATA', null);
            //记录行为
            action_log('update_config', 'uni_account_config', $id, is_login());
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }
}