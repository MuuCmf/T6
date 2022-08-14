<?php

namespace app\admin\controller;

use think\App;
use think\facade\Db;
use think\facade\View;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminListBuilder;
use app\common\model\ScoreLog as ScoreLogModel;
use app\common\model\ScoreType as ScoreTypeModel;

/**
 * 积分相关功能控制器
 */
class Score extends Admin {

    protected $scoreLogModel;
    protected $scoreTypeModel;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->scoreLogModel = new ScoreLogModel();
        $this->scoreTypeModel = new ScoreTypeModel();
    }

    /**
     * 积分日志
     * @param  integer $r [description]
     * @param  integer $p [description]
     * @return [type]     [description]
     */
    public function log($r=20){

        $aUid=input('uid',0,'');
        $map=[];
        if($aUid){
            $map['uid']=$aUid;
        }
        
        $scoreLog = $this->scoreLogModel->where($map)->order('create_time desc')->paginate($r);
        $totalCount = $this->scoreLogModel->count();
        //分页HTML
        $page = $scoreLog->render();
        //转数组处理
        $scoreLog = $scoreLog->toArray()['data'];

        $scoreTypes = $this->scoreTypeModel->getTypeListByIndex();

        foreach ($scoreLog as &$v) {
            if(empty($v['uid'])) $v['uid'] = 0;
            $v['adjustType'] = $v['action']== 'inc'?'增加':'减少';
            $v['scoreType'] = $scoreTypes[$v['type']]['title'];
            $class = $v['action'] == 'inc' ? 'text-success':'text-danger';
            $v['value']='<span class="'.$class.'">' .  ($v['action'] == 'inc'?'+':'-'). $v['value']. $scoreTypes[$v['type']]['unit'].'</span>';
            $v['finally_value'] = $v['finally_value']. $scoreTypes[$v['type']]['unit'];
        }
        unset($v);

        $builder = new AdminListBuilder();

        $builder->title('积分日志');
        $builder->data($scoreLog);
        $builder->page($page);
        $builder
            ->keyId()
            ->keyUid()
            ->keyText('scoreType','积分类型')
            ->keyText('adjustType','调整类型')
            ->keyHtml('value','积分变动')
            ->keyText('finally_value','积分最终值')
            ->keyText('remark','变动描述')
            ->keyCreateTime();

        $builder->search('搜索','uid','text','输入UID');
        $builder->button('清空日志',['url'=>url('clear'),'class'=>'btn btn-danger ajax-get confirm']);
    
        $builder->display();
    }

    /**
     * 清空积分日志
     */
    public function clear()
    {
        Db::name('ScoreLog')->where('id', '>', 0)->delete();
        return $this->success('清空成功。',url('scoreLog'));
    }

    /**
     * 积分列表
     * @return [type] [description]
     */
    public function type()
    {
        //读取数据
        $map[] = ['status' ,'>', -1];
        $list = $this->scoreTypeModel->getTypeList($map);
        //dump($list);
        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title('积分类型')
            ->suggest('id<=4的不能删除')
            ->buttonNew(url('editType'))
            ->setStatusUrl(url('setTypeStatus'))
            ->buttonEnable()
            ->buttonDisable()
            ->buttonDelete(url('delType'),'删除')
            ->keyId()
            ->keyText('title', '名称')
            ->keyText('unit', '单位')
            ->keyStatus()
            ->keyDoActionEdit('editType?id=###')
            ->data($list)
            ->display();
    }

    /**
     * 编辑积分类型
     */
    public function editType()
    {
        $aId = input('id', 0, 'intval');
        
        if (request()->isPost()) {
            $data['title'] = input('post.title', '', 'text');
            $data['status'] = input('post.status', 1, 'intval');
            $data['unit'] = input('post.unit', '', 'text');

            if ($aId != 0) {
                $data['id'] = $aId;
                $res = $this->scoreTypeModel->editType($data);
            } else {
                $res = $this->scoreTypeModel->addType($data);
            }
            if ($res) {
                return $this->success(($aId == 0 ? lang('Add') : lang('Edit')) . lang('Success'));
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
    public function setTypeStatus($ids, $status)
    {
        $ids = array_unique((array)$ids);
        $ids = implode(',',$ids);
        $rs = $this->scoreTypeModel->where('id','in', $ids)->update(['status' => $status]);
        if ($rs) {
            return $this->success('设置成功', $_SERVER['HTTP_REFERER']); 
        }else{
            return $this->error('设置失败');
        }
    }

    /**
     * 删除积分类型
     */
    public function delType()
    {
        $ids = input('ids/a');
        $res = $this->scoreTypeModel->delType($ids);
        if ($res) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

}