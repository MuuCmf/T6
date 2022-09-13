<?php
namespace app\admin\controller;

use think\App;
use think\facade\Db;
use think\facade\View;

use app\common\model\Action as ActionModel;
use app\common\model\ActionLimit as ActionLimitModel;
use app\common\model\Module as ModuleModel;
use app\common\model\ScoreType as ScoreTypeModel;

/**
 * 行为控制器
 */
class Action extends Admin {

    /**
     * 行为日志列表
     */
    public function log(){
        //获取列表数据
        $aUid=input('get.uid',0,'intval');
        if($aUid) $map[] = ['uid', '=', $aUid];

        //按时间和行为筛选
        $sTime = input('sTime',0,'text');
        $eTime = input('eTime',0,'text');
        $aSelect = input('select',0,'intval');
        if($sTime && $eTime) {
            $map[] = ['create_time','between',[strtotime($sTime),strtotime($eTime)]];
        }
        if($aSelect) {
            $map[] = ['action_id','=',$aSelect];
        }

        $map[]    =   ['status','>', -1];

        list($list,$pager)   =   $this->commonLists('action_log', $map);
        
        $list = $list->toArray()['data'];
        int_to_string($list);
        
        foreach ($list as $key => &$value){
            //$model_id                  =   get_document_field($value['model'],"name","id");
            //$list[$key]['model_id']    =   $model_id ? $model_id : 0;
            $list[$key]['ip'] = $value['action_ip'];
        }
        unset($value);

        $actionList = Db::name('Action')->select();
        View::assign('action_list', $actionList);

        View::assign('_list', $list);
        View::assign('pager', $pager);
        $this->setTitle('日志列表');

        return View::fetch();
    }

    /**
     * 查看行为日志
     */
    public function detail($id = 0){

        empty($id) && $this->error('参数错误');
        $info = Db::name('ActionLog')->find($id);
        View::assign('info', $info);

        $this->setTitle('行为日志详情');

        return View::fetch();
    }

    /**
     * 删除日志
     * @param mixed $ids
     */
    public function remove($ids = 0){
        if(empty($ids)){
            return $this->error('参数错误');
        }
        if(is_array($ids)){
            $map[] = ['id', 'in', $ids];
        }
        if (is_numeric($ids)){
            $map[] = ['id', '=', $ids];
        }
        $res = Db::name('ActionLog')->where($map)->delete();
        if($res !== false){
            return $this->success('删除成功');
        }else {
            return $this->error('删除失败');
        }
    }

    /**
     * 清空日志
     */
    public function clear(){
        $res = Db::execute('TRUNCATE TABLE ' . config('database.connections.mysql.prefix') . 'action_log');
        
        if($res !== false){
            return $this->success('日志清理成功');
        }else {
            return $this->error('清理失败');
        }
    }

    /**
     * 导出csv
     */
    public function csv()
    {
        $aIds = input('ids','','text');

        if($aIds){
            $aIds = explode(',',$aIds);
        }
        if(is_array($aIds) && count($aIds)) {
            $map[] = ['id', 'in', $aIds];
        } else {
            $map[] = ['status', '=', 1];
        }

        $list = Db::name('ActionLog')->where($map)->order('create_time asc')->select()->toArray();
        //dump($list);exit;
        
        $data = 'id,行为名称,执行者,执行者IP,日志内容,执行时间'."\n";
        foreach ($list as $val) {
            $val['create_time'] = time_format($val['create_time']);
            $data .= $val['id'].",".get_action($val['action_id'], 'title').",".get_nickname($val['uid'])."," .$val['action_ip'] .",".$val['remark'].",".$val['create_time']."\n";
        }
        $data = iconv('utf-8', 'gb2312', $data);
        $filename = date('Ymd').'.csv'; //设置文件名
        $this->export_csv($filename, $data); //导出
    }

    private function export_csv($filename, $data)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        echo $data;
    }


    /**
     * 用户行为列表
     */
    public function action()
    {
        // $aModule = $this->parseSearchKey('module');

        // is_null($aModule) && $aModule = -1;
        // if ($aModule != -1) {
        //     $map['module'] = $aModule;
        // }
        // unset($_REQUEST['module']);
        // View::assign('current_module', $aModule);

        $ModuleModel = new ModuleModel();
        //获取列表数据
        $map[] = ['status','>', -1];
        $ActionModel = new ActionModel();
        $list = $ActionModel->getListByPage($map,'update_time desc','*',20);
        $page = $list->render();
        View::assign('page',$page);
        $list = $list->toArray();
        lists_plus($list['data']);
        int_to_string($list['data']);
        View::assign('list', $list);

        $modules = $ModuleModel->getAll([
            ['is_setup' , '=', 1]
        ]);
        $modules = array_merge([array('name' => '', 'alias' => '系统')], $modules);

        View::assign('modules', $modules);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->setTitle('行为日志');

        return View::fetch();
    }

    /**
     * 新增、编辑行为
     * @author dameng <59262424@qq.com>
     */
    public function edit()
    {
        $ActionModel = new ActionModel();
        $ModuleModel = new ModuleModel();
        if(request()->isPost()){
            /* 获取数据对象 */
            $data = input('');
            
            $res = $ActionModel->editAction($data);
            if (!$res) {
                return $this->error($ActionModel->getError());
            } else {
                return $this->success($res['id'] ? '更新成功！' : '新增成功', $res ,Cookie('__forward__'));
            }
        }else{
            $id = input('id');

            if($id){
                $data = $ActionModel->find($id);
                $data['rule'] = unserialize( $data['rule']);

            }else{
                //初始默认数据
                $data = [
                    'name'=>'',
                    'title'=>'',
                    'log'=>'',
                    'module'=>'',
                    'remark'=>'',
                    'rule'=>'',
                    'id'=>''
                ];
            }

            View::assign('data', $data);
            $scoreTypeModel = new ScoreTypeModel();
            $score = $scoreTypeModel->getTypeList(array('status'=>1));
            View::assign('score', $score);
            // 获取所有应用模型列表
            $modules = $ModuleModel->getAll();
            View::assign('modules', $modules);

            $this->setTitle('编辑行为规则');

            return View::fetch();
        }
    }

    public function setStatus()
    {
        $ids = input('ids/a');
        !is_array($ids)&&$ids=explode(',', $ids);
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
        $ActionModel = new ActionModel();
        $res = $ActionModel->where('id', 'in', $ids)->update($data);
        if($res){
            return $this->success($title . '成功');
        }else{
            return $this->error($title . '失败');
        }  
    }

    /**
     * 行为限制列表
     */
    public function limit()
    {
        $this->setTitle('行为限制');
        $action_name = input('get.action','','text') ;
        !empty($action_name) && $map['action_list'] = ['like', '%[' . $action_name . ']%','','or'];
        
        $ActionModel = new ActionModel();
        $ActionLimitModel = new ActionLimitModel();

        //读取规则列表
        $map[] = ['status', '>=',  0];
        $list = $ActionLimitModel->getListByPage($map);
        // 获取分页显示
        $page = $list->render();

        $timeUnit = get_time_unit();
        // 处理数据
        foreach($list as &$val){
            $val['time'] = $val['time_number']. $timeUnit[$val['time_unit']];
            $val['action_list'] = $ActionModel->getActionName($val['action_list']);
            empty( $val['action_list']) &&  $val['action_list'] = '所有行为';

            $val['punish'] = $ActionLimitModel->getPunishName($val['punish']);
        }
        unset($val);

        //显示页面
        View::assign('list', $list);
        View::assign('page', $page);
        
        return View::fetch();
    }

    /**
     * [editLimit description]
     * @return [type] [description]
     */
    public function editLimit()
    {
        $aId = input('id', 0, 'intval');
        $ActionModel = new ActionModel();
        $ActionLimitModel = new ActionLimitModel();
        $ModuleModel = new ModuleModel();

        if (request()->isPost()) {

            $data['title'] = input('post.title', '', 'text');
            $data['name'] = input('post.name', '', 'text');
            $data['frequency'] = input('post.frequency', 1, 'intval');
            $data['time_number'] = input('post.time_number', 1, 'intval');
            $data['time_unit'] = input('post.time_unit', '', 'text');
            $data['punish'] = input('post.punish/a', array());
            $data['if_message'] = input('post.if_message', '', 'text');
            $data['message_content'] = input('post.message_content', '', 'text');
            $data['action_list'] = input('post.action_list/a');
            $data['status'] = input('post.status', 1, 'intval');
            $data['module'] = input('post.module', '', 'text');
            $data['id'] = $aId;
            
            $data['punish'] = implode(',', $data['punish']);
            if($data['action_list']){
                foreach($data['action_list'] as &$v){
                    $v = '['.$v.']';
                }
                unset($v);
                $data['action_list'] = implode(',', $data['action_list']);
            }

            $res = $ActionLimitModel->edit($data);
            
            if($res){
                return $this->success(($aId == 0 ? '新增' : '编辑') . '成功', '', url('limit'));
            }else{
                return $this->error('提交失败');
            }
        } else {

            // 获取所有模块
            $modules = $ModuleModel->getAll();
            foreach($modules as $k=>$v){
                $module[$v['name']] = $v['alias'];
            }
            View::assign('modules', $modules);

            // 获取数据
            if ($aId != 0) {
                $limit = $ActionLimitModel->where(['id' => $aId])->find();
                $limit['punish'] = explode(',', $limit['punish']);
                $limit['action_list'] = str_replace('[','',$limit['action_list']);
                $limit['action_list'] = str_replace(']','',$limit['action_list']);
                $limit['action_list'] = explode(',', $limit['action_list']);

            } else {
                $limit = [
                    'status' => 1,
                    'time_number' => 1,
                    'time_unit' => [],
                ];
            }
            //dump($limit);
            // 处罚方式数组
            $opt_punish = $ActionLimitModel->punish;
            // 行为数组
            $opt_action = $ActionModel->getActionOpt();

            View::assign('opt_punish',$opt_punish);
            View::assign('opt_action',$opt_action);
            View::assign('limit', $limit);

            return View::fetch();
        }
    }

    /**
     * 行为限制状态
     */
    public function limitStatus(int $status = 0)
    {
        $ids = array_unique((array)input('ids/a', 0));
        $ids = is_array($ids) ? implode(',', $ids) : $ids;

        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }

        $map = ['id' => ['in', $ids]];
        
        switch (strtolower($status)) {
            case 0:
                return $this->forbid('action_limit', $map);
                break;
            case 1:
                return $this->resume('action_limit', $map);
                break;
            case -1:
                return $this->delete('action_limit', $map);
                break;
            default:
                return $this->error('参数错误');
        }
    }

}
