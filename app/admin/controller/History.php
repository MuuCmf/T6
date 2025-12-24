<?php

namespace app\admin\controller;

use app\common\model\History as HistoryModel;
use app\common\logic\History as HistoryLogic;
use app\common\model\Module as ModuleModel;
use think\facade\View;
use think\facade\Cache;

/**
 * 浏览记录管理控制器
 * @package app\admin\controller
 */
class History extends Admin
{
    /** @var HistoryModel 浏览记录模型 */
    protected $historyModel;
    
    /** @var HistoryLogic 浏览记录逻辑 */
    protected $historyLogic;
    
    /** @var ModuleModel 模块模型 */
    protected $moduleModel;

    /**
     * 构造函数 - 使用依赖注入
     * @param HistoryModel $historyModel
     * @param HistoryLogic $historyLogic
     * @param ModuleModel $moduleModel
     */
    public function __construct(
        HistoryModel $historyModel = null,
        HistoryLogic $historyLogic = null,
        ModuleModel $moduleModel = null
    ) {
        parent::__construct();
        $this->historyModel = $historyModel ?? new HistoryModel();
        $this->historyLogic = $historyLogic ?? new HistoryLogic();
        $this->moduleModel = $moduleModel ?? new ModuleModel();
    }

    /**
     * 浏览记录列表
     * @return string|\think\response\Json
     */
    public function list()
    {
        // 获取请求参数
        $app = input('get.app', 'all', 'trim');
        $keyword = input('keyword', '', 'trim');
        $rows = input('rows', 20, 'intval');
        
        // 限制分页数量
        $rows = min(max($rows, 1), 100);
        
        View::assign('keyword', $keyword);
        View::assign('rows', $rows);
        
        // 构建查询条件
        $map = $this->buildQueryMap($app, $keyword);
        
        // 获取分页列表
        $lists = $this->historyModel->getListByPage($map, 'id desc, create_time desc', '*', $rows);
        
        // 分页按钮
        $pager = $lists->render();
        
        // 格式化数据
        $lists = $lists->toArray();
        $lists['data'] = $this->formatListData($lists['data']);
        
        // AJAX 请求返回 JSON
        if (request()->isAjax()) {
            return $this->success('success', $lists);
        }
        
        // 获取所有模块（使用缓存）
        $allModule = $this->getAllModulesCached();
        
        View::assign([
            'lists' => $lists['data'],
            'pager' => $pager,
            'all_module' => $allModule,
            'app' => $app
        ]);
        
        $this->setTitle('浏览记录');
        return View::fetch();
    }
    
    /**
     * 构建查询条件
     * @param string|null $app 应用标识
     * @param string|null $keyword 关键词
     * @return array
     */
    protected function buildQueryMap(?string $app, ?string $keyword): array
    {
        $map = [
            ['shopid', '=', $this->shopid],
            ['status', '=', 1]
        ];
        
        // 应用筛选
        if ($app !== null && $app !== 'all' && $app !== '') {
            $map[] = ['app', '=', $app];
        }
        
        // 关键词搜索
        if ($keyword !== null && $keyword !== '') {
            $map[] = ['metadata', 'like', '%' . $keyword . '%'];
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
            $item = $this->historyLogic->formatData($item);
        }
        unset($item);
        
        return $data;
    }
    
    /**
     * 获取所有模块（带缓存）
     * @return array
     */
    protected function getAllModulesCached(): array
    {
        $cacheKey = 'all_modules_' . $this->shopid;
        
        return Cache::remember($cacheKey, function() {
            return $this->moduleModel->getAll([]);
        }, 3600); // 缓存 1 小时
    }

    /**
     * 设置状态
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
            return $this->error('请选择要操作的记录');
        }
        
        $status = input('status', 0, 'intval');
        
        // 验证状态值
        $allowedStatus = [-1, 0, 1];
        if (!in_array($status, $allowedStatus, true)) {
            return $this->error('无效的状态值');
        }
        
        $title = $status === -1 ? '删除' : '更新';
        
        // 执行更新
        $res = $this->historyModel
            ->where('id', 'in', $ids)
            ->where('shopid', '=', $this->shopid) // 防止跨店铺操作
            ->update(['status' => $status]);
        
        if ($res !== false) {
            // 清理相关缓存
            $this->clearRelatedCache();
            return $this->success($title . '成功', ['affected_rows' => $res], 'refresh');
        }
        
        return $this->error($title . '失败');
    }
    
    /**
     * 清理相关缓存
     * @return void
     */
    protected function clearRelatedCache(): void
    {
        $cacheKey = 'all_modules_' . $this->shopid;
        Cache::delete($cacheKey);
    }
}
