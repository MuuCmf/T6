<?php

namespace app\admin\controller;

use think\facade\View;
use think\facade\Cache;
use app\common\model\Module as ModuleModel;
use app\common\model\Announce as AnnounceModel;
use app\common\logic\Announce as AnnounceLogic;
use app\admin\validate\Common;
use think\exception\ValidateException;

/**
 * 公告管理控制器
 * @package app\admin\controller
 */
class Announce extends Admin
{
    /** @var ModuleModel 模块模型 */
    protected $moduleModel;
    
    /** @var AnnounceModel 公告模型 */
    protected $announceModel;
    
    /** @var AnnounceLogic 公告逻辑 */
    protected $announceLogic;

    /**
     * 构造方法 - 使用依赖注入
     * @param ModuleModel|null $moduleModel
     * @param AnnounceModel|null $announceModel
     * @param AnnounceLogic|null $announceLogic
     */
    public function __construct(
        ?ModuleModel $moduleModel = null,
        ?AnnounceModel $announceModel = null,
        ?AnnounceLogic $announceLogic = null
    ) {
        parent::__construct();
        $this->moduleModel = $moduleModel ?? new ModuleModel();
        $this->announceModel = $announceModel ?? new AnnounceModel();
        $this->announceLogic = $announceLogic ?? new AnnounceLogic();
        // 设置页面title
        $this->setTitle('公告管理');
    }

    /**
     * 公告列表
     * @return string|\think\response\Json
     */
    public function list()
    {
        // 获取搜索关键词
        $keyword = input('keyword', '', 'trim');
        $rows = input('rows', 20, 'intval');
        
        // 限制分页数量
        $rows = min(max($rows, 1), 100);
        
        View::assign('keyword', $keyword);
        View::assign('rows', $rows);
        
        // 构建查询条件
        $map = $this->buildQueryMap($keyword);
        
        // 获取分页列表
        $lists = $this->announceModel->getListByPage(
            $map, 
            'sort desc, create_time desc', 
            '*', 
            $rows
        );
        
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        // 格式化数据
        $lists['data'] = $this->formatListData($lists['data']);
        
        // AJAX 请求返回 JSON
        if (request()->isAjax()) {
            return $this->success('success', $lists);
        }
        
        View::assign('pager', $pager);
        View::assign('lists', $lists);
        
        // 记录当前列表页的cookie
        cookie('__forward__', request()->url(true));
        
        return View::fetch();
    }
    
    /**
     * 构建查询条件
     * @param string|null $keyword 搜索关键词
     * @return array
     */
    protected function buildQueryMap(?string $keyword): array
    {
        $map = [
            ['status', '>', -1],
            ['shopid', '=', 0]
        ];
        
        // 关键词搜索
        if ($keyword !== null && $keyword !== '') {
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }
        
        return $map;
    }
    
    /**
     * 格式化列表数据
     * @param array $data
     * @return array
     */
    protected function formatListData(array $data): array
    {
        foreach ($data as &$item) {
            $item = $this->announceLogic->formatData($item);
        }
        unset($item);
        
        return $data;
    }

    /**
     * 编辑、新增公告
     * @return string|\think\response\Json
     */
    public function edit()
    {
        $id = input('id', 0, 'intval');
        $title = $id > 0 ? "编辑" : "新建";
        $teminal = input('teminal', 'mobile', 'trim') ?? 'mobile';
        
        View::assign('title', $title);
        View::assign('teminal', $teminal);

        if (request()->isPost()) {
            return $this->handleEdit((int)$id, $title);
        }
        
        return $this->showEditForm((int)$id, $teminal);
    }
    
    /**
     * 处理编辑提交
     * @param int $id
     * @param string $title
     * @return \think\response\Json
     */
    protected function handleEdit(int $id, string $title)
    {
        $data = input();
        $data['shopid'] = $this->shopid;
        $data['uid'] = get_uid();
        
        // 数据验证
        try {
            validate(Common::class)->scene('announce')->check([
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        
        // 处理链接数据
        $data['link_to'] = $this->buildLinkData($data);
        
        // 写入数据表
        $res = $this->announceModel->edit($data);
        
        if ($res) {
            return $this->success($title . '成功', $res, cookie('__forward__'));
        }
        
        return $this->error($title . '失败');
    }
    
    /**
     * 构建链接数据
     * @param array $data
     * @return string
     */
    protected function buildLinkData(array $data): string
    {
        if (empty($data['link_type']) && empty($data['link_title'])) {
            return '';
        }
        
        $linkTo = [
            'app' => $data['link_app'] ?? '',
            'type' => $data['link_type'] ?? '',
            'title' => $data['link_title'] ?? '',
            'type_title' => $data['link_type_title'] ?? '',
            'param' => json_decode($data['link_param'] ?? '{}', true)
        ];
        
        return json_encode($linkTo, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * 显示编辑表单
     * @param int $id
     * @param string $teminal
     * @return string
     */
    protected function showEditForm(int $id, string $teminal): string
    {
        if ($id > 0) {
            $data = $this->announceModel->getDataById($id);
            $data = $this->announceLogic->formatData($data);
            $teminal = $data['teminal'] ?? $teminal;
            
            // 链接参数二次处理
            if (!empty($data['link'])) {
                $link = $data['link'];
                $link['param'] = json_encode($link['param'], JSON_UNESCAPED_UNICODE);
                $data['link'] = $link;
            }
        } else {
            // 初始化数据
            $data = $this->getDefaultData($teminal);
        }
        
        View::assign('data', $data);
        
        // 获取Micro应用是否安装
        $microIsSetup = $this->moduleModel->checkInstalled('micro');
        View::assign('micro_is_setup', $microIsSetup);
        
        if ($microIsSetup) {
            $this->loadMicroLinks($teminal);
        }
        
        return View::fetch();
    }
    
    /**
     * 获取默认数据
     * @param string $teminal
     * @return array
     */
    protected function getDefaultData(string $teminal): array
    {
        return [
            'id' => 0,
            'teminal' => $teminal,
            'type' => 1,
            'title' => '',
            'content' => '',
            'cover' => '',
            'status' => 1,
            'sort' => 0,
        ];
    }
    
    /**
     * 加载 Micro 链接数据
     * @param string $teminal
     * @return void
     */
    protected function loadMicroLinks(string $teminal): void
    {
        bind('micro\\LinksSevice', 'app\\micro\\service\\Link');
        $links = app('micro\\LinksSevice')->getAllLinks($teminal);
        View::assign('links', $links);
        
        $linkStaticTmpl = app('micro\\LinksSevice')->getStaticTmpl($teminal);
        View::assign('link_static_tmpl', $linkStaticTmpl);
    }

    /**
     * 状态管理
     * @return \think\response\Json
     */
    public function status()
    {
        // 获取参数
        $ids = input('ids/a', []);
        if (!is_array($ids)) {
            $ids = explode(',', (string)$ids);
        }
        
        // 验证 IDs
        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) {
            return $this->error('请选择要操作的公告');
        }
        
        $status = input('status', 0, 'intval');
        
        // 验证状态值
        $allowedStatus = [-1, 0, 1];
        if (!in_array($status, $allowedStatus, true)) {
            return $this->error('无效的状态值');
        }
        
        // 根据状态设置标题
        $statusTitles = [
            -1 => '删除',
            0 => '禁用',
            1 => '启用'
        ];
        $title = $statusTitles[$status] ?? '更新';
        
        // 执行更新
        $res = $this->announceModel
            ->where('id', 'in', $ids)
            ->where('shopid', '=', 0) // 公告为全局
            ->update(['status' => $status]);
        
        if ($res !== false) {
            return $this->success($title . '成功', ['affected_rows' => $res]);
        }
        
        return $this->error($title . '失败');
    }
}
