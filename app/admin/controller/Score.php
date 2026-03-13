<?php

namespace app\admin\controller;

use think\App;
use think\facade\Db;
use think\facade\View;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\common\model\Member as MemberModel;
use app\common\model\ScoreLog as ScoreLogModel;
use app\common\model\ScoreType as ScoreTypeModel;

/**
 * 积分相关功能控制器
 */
class Score extends Admin
{
    protected $MemberModel;
    protected $scoreLogModel;
    protected $scoreTypeModel;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->MemberModel = new MemberModel();
        $this->scoreLogModel = new ScoreLogModel();
        $this->scoreTypeModel = new ScoreTypeModel();
    }

    /**
     * 积分日志
     * @param  integer $r [description]
     * @param  integer $p [description]
     * @return [type]     [description]
     */
    public function log()
    {
        $keyword = input('keyword', '', 'trim');
        $rows = input('rows', 20, 'intval');
        View::assign('rows', $rows);
        $where = [];
        if (!empty($keyword)) {
            $where[] = function($query) use ($keyword) {
                $query->where('m.nickname', 'like', '%' . $keyword . '%')
                      ->whereOr('m.mobile', 'like', '%' . $keyword . '%')
                      ->whereOr('m.uid', 'like', '%' . $keyword . '%');
            };
        }

        $scoreLog = $this->scoreLogModel
        ->alias('sl')
        ->join('member m', 'sl.uid = m.uid')
        ->field('sl.*, m.nickname, m.mobile')
        ->order('sl.create_time desc')
        ->where($where)
        ->paginate([
                'list_rows' => $rows,
                'query' => [
                    'keyword' => $keyword
                ],
            ], false);
        
        $page = $scoreLog->render();
        // 转数组处理
        $scoreLog = $scoreLog->toArray();
        // 处理积分类型名称
        $scoreTypes = $this->scoreTypeModel->getTypeListByIndex();
        // 处理积分变动类型
        foreach ($scoreLog['data'] as &$v) {
            if (empty($v['uid'])){
                $v['uid'] = 0;
            }else{
                $v['user_info'] = $this->MemberModel->info($v['uid'], '*');
            }
            $v['adjust_type'] = $v['action'] == 'inc' ? '增加' : '减少';
            $v['score_type'] = $scoreTypes[$v['type']]['title'];
            $v['value'] = ($v['action'] == 'inc' ? '+' : '-') . $v['value'] . $scoreTypes[$v['type']]['unit'];
            $v['final_value'] = $v['finally_value'] . $scoreTypes[$v['type']]['unit'];
            if (!empty($v['create_time'])) {
                $v['create_time_str'] = time_format($v['create_time']);
                $v['create_time_friendly_str'] = friendly_date($v['create_time']);
            }
        }
        unset($v);

        // ajax请求返回
        if (request()->isAjax()){
            return $this->success('success',$scoreLog);
        }

        $builder = new AdminListBuilder();
        $builder->title('积分日志');
        $builder->data($scoreLog['data']);
        $builder->page($page);
        $builder
            ->keyId()
            ->keyUid()
            ->keyText('score_type', '积分类型')
            ->keyText('adjust_type', '调整类型')
            ->keyHtml('value', '积分变动')
            ->keyText('finally_value', '积分最终值')
            ->keyText('remark', '变动描述')
            ->keyCreateTime();

        $builder->search('搜索', 'uid', 'text', '输入UID');
        $builder->button('清空日志', ['url' => url('clear'), 'class' => 'btn btn-danger ajax-get confirm']);

        $builder->display();
    }

    /**
     * 清空积分日志
     */
    public function clear()
    {
        Db::name('ScoreLog')->where('id', '>', 0)->delete();
        return $this->success('清空成功。', url('scoreLog'));
    }

    /**
     * 积分列表
     * @return [type] [description]
     */
    public function type()
    {
        $status = input('status', '', 'text');
        $keyword = input('keyword', 0, '');
        $map = [];
        if (!empty($keyword)) {
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }
        //读取数据
        if (empty($status)) {
            $map[] = ['status', 'in', [0, 1]];
        } else {
            $map[] = ['status', '=', intval($status)];
        }
        $list = $this->scoreTypeModel->getTypeList($map);

        // ajax请求返回
        if (request()->isAjax()){
            return $this->success('success',$list);
        }

        // 记录当前列表页的cookie
        cookie('__forward__', $_SERVER['REQUEST_URI']);
        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title('积分类型')
            ->suggest('id<=4的不能删除')
            ->buttonNew(url('typeEdit'))
            ->setStatusUrl(url('typeStatus'))
            ->buttonEnable()
            ->buttonDisable()
            ->buttonDelete(url('typeDel'), '删除')
            ->keyId()
            ->keyText('title', '名称')
            ->keyText('unit', '单位')
            ->keyStatus()
            ->keyDoActionEdit('typeEdit?id=###')
            ->keyDoActionDelete('typeDel?ids=###')
            ->data($list)
            ->display();
    }

    /**
     * 编辑积分类型
     */
    public function typeEdit()
    {
        $aId = input('id', 0, 'intval');

        if (request()->isPost()) {
            $data['title'] = input('post.title', '', 'text');
            $data['status'] = input('post.status', 1, 'intval');
            $data['unit'] = input('post.unit', '', 'text');

            if (!empty($aId)) {
                $data['id'] = $aId;
                $res = $this->scoreTypeModel->editType($data);
            } else {
                $res = $this->scoreTypeModel->addType($data);
            }
            if ($res) {
                return $this->success(($aId == 0 ? lang('Add') : lang('Edit')) . lang('Success'), $res, cookie('__forward__'));
            } else {
                return $this->error(($aId == 0 ? lang('Add') : lang('Edit')) . lang('Failed'));
            }
        } else {

            if ($aId != 0) {
                $type = $this->scoreTypeModel->getType(['id' => $aId]);
            } else {
                $type = ['status' => 1, 'sort' => 0];
            }

            $builder = new AdminConfigBuilder();
            $builder
                ->title(($aId == 0 ? '新增' : '编辑') . '积分类型')
                ->keyId()
                ->keyText('title', '名称')
                ->keyText('unit', '单位')
                ->keySelect('status', '状态', null, array(-1 => '删除', 0 => '禁用', 1 => '启用'))
                ->data($type)
                ->buttonSubmit(url('editType'))
                ->buttonBack()
                ->display();
        }
    }

    /**
     * 设置积分类型状态
     */
    public function typeStatus($ids, $status)
    {
        $ids = input('ids/a');
        !is_array($ids) && $ids = explode(',', (string)$ids);
        $ids = array_unique((array)$ids);
        
        $status = input('status', 0, 'intval');
        $title = '更新';
        if($status == 0){
            $title = '禁用';
        }
        if($status == 1){
            $title = '启用';
        }
        if($status == -1){
            $title = '删除';
        }
        $data['status'] = $status;
        
        $res = $this->scoreTypeModel->where('id', 'in', $ids)->update($data);
        if ($res) {
            return $this->success($title . '成功');
        } else {
            return $this->error($title . '失败');
        }
    }

    /**
     * 删除积分类型
     */
    public function typeDel()
    {
        $ids = input('ids');
        !is_array($ids) && $ids = explode(',', (string)$ids);
        $ids = array_unique((array)$ids);

        $res = $this->scoreTypeModel->delType($ids);
        if ($res) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }
}
