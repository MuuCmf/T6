<?php
namespace app\channel\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use think\facade\Db;
use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminConfigBuilder;
use app\admin\builder\AdminSortBuilder;
use app\common\model\SeoRule;

class Seo extends MuuAdmin
{
    public function lists()
    {
        //读取规则列表
        $app=input('get.app','','text');
        $map = [
            ['status', 'in', [0,1]]
        ];
        if(!empty($aApp)){
            $map[]= ['app', '=', $app];
        }

        list($ruleList,$page) = $this->commonLists('SeoRule', $map, 'sort asc');
        $page = $ruleList->render();

        $module = $this->moduleModel->getAll();
        $app = array();
        foreach ($module as $m) {
            if ($m['is_setup'])
                $app[] =array('id'=>$m['name'],'value'=>$m['alias']) ;
        }
        $ruleList = $ruleList->toArray()['data'];
        //显示页面
        $builder = new AdminListBuilder();
        $builder->setSelectPostUrl(url('lists'));
        $builder
            ->title('Seo规则')
            ->setStatusUrl(url('status'))
            ->buttonNew(url('edit'))
            ->buttonEnable()
            ->buttonDisable()
            ->buttonDelete()
            ->keyTitle()
            ->keyText('app', '应用')
            ->keyText('controller', '控制器')
            ->keyText('action', '方法')
            ->keyText('seo_title', 'Seo标题')
            ->keyText('seo_keywords', 'Seo关键字')
            ->keyText('seo_description', 'Seo描述')
            ->select('应用筛选', 'app', 'select', '', '', '', array_merge(array(array('id' => '', 'value' => lang('全部'))), $app))
            ->keyStatus()
            ->keyDoActionEdit('edit?id=###')
            ->data($ruleList)
            ->page($page)
            ->display();
    }

    /**
     * 编辑规则
     */
    public function edit($id = 0)
    {
        //判断是否为编辑模式
        $isEdit = $id ? true : false;
        if (request()->isPost()) {
            $params = input();
            //写入数据库
            $data = [
                'title' => $params['title'], 
                'app' => strtolower($params['app']), 
                'controller' => strtolower($params['controller']), 
                'action' => strtolower($params['action2']), 
                'seo_title' => $params['seo_title'], 
                'seo_keywords' => $params['seo_keywords'],
                'seo_description' => $params['seo_description'], 
                'status' => $params['status']
            ];

            //查询是否包含相同规则
            $has_map = [
                ['app', '=', $data['app']],
                ['controller', '=', $data['controller']],
                ['action', '=', $data['action']],
                ['status', 'in', [0,1]]
            ];

            $has_rule = Db::name('SeoRule')->where($has_map)->find();
            if($has_rule && !$isEdit){
                return $this->error('已存在相同规则');
            }

            if ($isEdit) {
                $result = Db::name('SeoRule')->where(['id' => $id])->update($data);
            } else {
                $result = Db::name('SeoRule')->insert($data);
            }

            //如果失败的话，显示失败消息
            if (!$result) {
                return $this->error($isEdit ? '编辑失败' : '创建失败');
            }

            //显示成功信息，并返回规则列表
            return $this->success($isEdit ? '编辑成功' : '创建成功', $result, url('lists'));
        }

        //读取规则内容
        if ($isEdit) {
            $rule = Db::name('SeoRule')->where(['id' => $id])->find();
        } else {
            $rule = [
                'status' => 1,
                'action' => '',
                'summary'=> ''
            ];
        }
        $rule['action2'] = $rule['action'];
        $rule['summary'] = nl2br($rule['summary']);

        $modules = $this->moduleModel->getAll();
        $app = ['' => '全部应用'];
        foreach ($modules as $m) {
            if ($m['is_setup']) {
                $app[$m['name']] = lcfirst($m['alias']);//首字母改小写，兼容V1
            }
        }
        //显示页面
        $builder = new AdminConfigBuilder();
        $builder
            ->title($isEdit ? '编辑规则' : '创建规则')
            ->keyId()
            ->keyText('title', '标题', '规则标题')
            ->keySelect('app', '应用', '规则适用应用', $app)
            ->keyText('controller', '控制器', '规则适用控制器')
            ->keyText('action2', '方法', '规则适用方法')
            ->keyText('seo_title', 'SEO标题', '')
            ->keyTextArea('seo_keywords', 'SEO关键字', '')
            ->keyTextArea('seo_description', 'SEO描述', '')
            ->keyReadOnly('summary', '变量说明', '调用的时候必须写成{:xxx},其中xxx就是下方变量')
            ->keyStatus()
            ->data($rule)
            ->buttonSubmit(url('edit'))
            ->buttonBack()
            ->display();
    }

    /**
     * 配置状态
     */
    public function status($ids, $status)
    {
        $builder = new AdminListBuilder();
        return $builder->doSetStatus('SeoRule', $ids, $status);
    }

    public function doClear($ids)
    {
        $builder = new AdminListBuilder();
        return $builder->doDeleteTrue('SeoRule', $ids);
    }

}