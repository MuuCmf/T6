<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminSortBuilder;
use app\common\model\Module as ModuleModel;
use app\common\model\SeoRule as SeoRuleModel;
use app\admin\validate\Seo as SeoValidate;

class Seo extends Admin
{   
    protected $moduleModel;
    protected $seoRuleModel;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->moduleModel = new ModuleModel();
        $this->seoRuleModel = new seoRuleModel();
        $this->seoValidate = new SeoValidate();
    }

    /**
     * seo规则
     */
    public function index($page = 1, $r = 20)
    {
        //读取规则列表
        $app = input('get.app','','text');
        $map[] = ['status','>=', 0];
        if($app!=''){
            $map[] = ['app', '=' ,$app];
        }

        // 获取分页列表
        list($ruleList,$page) = $this->commonLists('SeoRule', $map, 'sort asc');
        $page = $ruleList->render();
        // 获取应用模块列表
        $module = $this->moduleModel->getAll();
        $app = [];
        foreach ($module as $m) {
            if ($m['is_setup'])
                $app[] = ['id'=> $m['name'],'value'=>$m['alias']];
        }
        $ruleList = $ruleList->toArray()['data'];
        //显示页面
        $builder = new AdminListBuilder();
        $builder->setSelectPostUrl(url('index'));
        $builder
            ->title(lang('seo.Seo rule'))
            ->setStatusUrl(url('status'))
            ->buttonNew(url('edit'))
            ->buttonEnable()
            ->buttonDisable()
            ->buttonDelete()
            ->buttonSort(url('sort'))
            ->keyId()
            ->keyTitle()
            ->keyText('app', lang('App'))
            ->keyText('controller', lang('seo.Controller'))
            ->keyText('action', lang('seo.Action'))
            ->keyText('seo_title', lang('seo.Seo title'))
            ->keyText('seo_keywords', lang('seo.Seo keywords'))
            ->keyText('seo_description', lang('seo.Seo description'))
            ->select('所属应用', 'app', 'select', '', '', '', array_merge([['id' => '', 'value' => lang('all')]], $app))
            ->keyStatus()
            ->keyDoActionEdit('edit?id=###')
            ->data($ruleList)
            ->page($page)
            ->display();
    }

    /**
     * 规则回收站
     */
    public function trash()
    {
        //读取规则列表
        $map[] = array('status' ,'=', -1);
        list($ruleList,$page) = $this->commonLists('SeoRule', $map, 'sort asc');
        $page = $ruleList->render();

        //显示页面
        $builder = new AdminListBuilder();
        $builder
        ->title(lang('seo.Seo rule recycling station'))
            ->setStatusUrl(url('status'))
            ->setDeleteTrueUrl(url('clear'))
            ->buttonRestore()
            ->buttonDeleteTrue()
            ->keyId()
            ->keyTitle()
            ->keyText('app', lang('App'))
            ->keyText('controller', lang('seo.Controller'))
            ->keyText('action', lang('seo.Action'))
            ->keyText('seo_title', lang('seo.Seo title'))
            ->keyText('seo_keywords', lang('seo.Seo keywords'))
            ->keyText('seo_description', lang('seo.Seo description'))
            ->data($ruleList)
            ->page($page)
            ->display();
    }

    /**
     * 设置状态
     */
    public function status($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('SeoRule', $ids, $status);
    }

    public function clear($ids)
    {
        $builder = new AdminListBuilder();
        $builder->doDeleteTrue('SeoRule', $ids);
    }

    /**
     * 规则排序
     */
    public function sort()
    {
        //读取规则列表
        $list = $this->seoRuleModel->where('status','>=', 0)->order('sort asc')->select();

        //显示页面
        $builder = new AdminSortBuilder();
        $builder
            ->title(lang('seo.Sort Seo rule'))
            ->data($list)
            ->buttonSubmit(url('sort'))
            ->buttonBack()
            ->display();
    }

    public function doSortRule($ids)
    {
        $builder = new AdminSortBuilder();
        $builder->doSort('SeoRule', $ids);
    }

    /**
     * 编辑、新增规则
     */
    public function edit($id = null)
    {
        if(request()->isPost()) {
            $input = input();
            // 判断是否为编辑模式
            $isEdit = $id ? true : false;

            $app = input('app', '', 'text');
            $controller = input('controller', '', 'text');
            $action = input('action', '', 'text');

            // 写入数据库
            $data = [
                'title' => input('title', '', 'text'), 
                'app' => $app, 
                'controller' => $controller, 
                'action' => $action, 
                'seo_title' => input('seo_title', '', 'text'), 
                'seo_keywords' => input('seo_keywords', '', 'text'),
                'seo_description' => input('seo_description', '', 'text'), 
                'status' => input('status',0 ,'intval')
            ];
            // 验证
            $result = $this->seoValidate->check($data);
            if(!$result){
                $this->error($this->seoValidate->getError());
            }
            if ($isEdit) {
                $result = $this->seoRuleModel->where(['id' => $id])->update($data);
            } else {
                $result = $this->seoRuleModel->insert($data);
            }
            $cacheKey = "seo_meta_{$app}_{$controller}_{$action}";
            cache($cacheKey,null);
            //如果失败的话，显示失败消息
            if (!$result) {
                $this->error($isEdit ? lang('Edit failed') : lang('Create failed'));
            }

            //显示成功信息，并返回规则列表
            $this->success($isEdit ? lang('Edit success') : lang('Create success'), url('index'));
        }else{
            //判断是否为编辑模式
            $isEdit = $id ? true : false;

            //读取规则内容
            if ($isEdit) {
                $rule = $this->seoRuleModel->where(['id' => $id])->find();
            } else {
                $rule = [
                    'status' => 1,
                    'action' => '',
                    'summary'=> ''
                ];
            }
            $rule['action'] = $rule['action'];
            $rule['summary'] = nl2br($rule['summary']);

            //显示页面
            $builder = new AdminConfigBuilder();
            $modules = $this->moduleModel->getAll();

            $app = ['' => lang('All app')];
            foreach ($modules as $m) {
                if ($m['is_setup']) {
                    $app[$m['name']] = lcfirst($m['alias']);//首字母改小写，兼容V1
                }
            }

            $builder
                ->title($isEdit ? lang('seo.Edit rule') : lang('seo.Add rule'))
                ->keyId()
                ->keyText('title', lang('seo.Name'), lang('seo.Name'))
                ->keySelect('app', lang('App'), lang('seo.Do not filled in all app'), $app)
                ->keyText('controller', lang('seo.Controller'), lang('seo.Do not fill in all controllers'))
                ->keyText('action2', lang('seo.Action'), lang('seo.Do not fill out all the actions'))
                ->keyText('seo_title', lang('seo.Seo title'), lang('seo.Do not fill in the use of the next rule,Support Variable'))
                ->keyText('seo_keywords', lang('seo.Seo keywords'), lang('seo.Do not fill in the use of the next rule,Support Variable'))
                ->keyTextArea('seo_description', lang('seo.Seo description'), lang('seo.Do not fill in the use of the next rule,Support Variable'))
                ->keyReadOnly('summary',lang('seo.Variable description'),lang('seo.Variable description vice'))
                ->keyStatus()
                ->data($rule)
                ->buttonSubmit(url('edit'))
                ->buttonBack()
                ->display();
        }
    }
}