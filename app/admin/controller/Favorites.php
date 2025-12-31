<?php

namespace app\admin\controller;

use app\common\model\Favorites as FavoritesModel;
use app\common\logic\Favorites as FavoritesLogic;
use think\facade\View;

class Favorites extends Admin
{
    protected $FavoritesModel;
    protected $FavoritesLogic;

    public function __construct()
    {
        parent::__construct();
        $this->FavoritesModel = new FavoritesModel();
        $this->FavoritesLogic = new FavoritesLogic();
    }

    /**
     * 浏览记录列表
     */
    public function list()
    {
        $app = input('get.app', 'all');
        $keyword = input('keyword', '');
        View::assign('keyword', $keyword);
        $rows = input('rows', 20, 'intval');
        View::assign('rows', $rows);
        $map = [
            ['shopid', '=', $this->shopid],
            ['status', '=', 1]
        ];
        if ($app != 'all')  $map[] = ['app', '=', $app]; //标识
        if (!empty($keyword)) {
            $map[] = ['metadata', 'like', '%' . $keyword . '%'];
        }
        // 获取分页列表
        $lists = $this->FavoritesModel->getListByPage($map, 'id desc create_time desc', '*', $rows);
        // 分页按钮
        $pager = $lists->render();
        // 格式化数据
        $lists = $lists->toArray();
        foreach ($lists['data'] as &$val) {
            $val = $this->FavoritesLogic->formatData($val);
        }
        unset($val);

        // ajax请求返回数据
        if (request()->isAjax()) {
            return $this->success('success', $lists);
        }

        //全部应用
        $module_map = [];
        $all_module = (new \app\common\model\Module())->getAll($module_map);
        unset($val);
        View::assign([
            'lists' =>  $lists['data'],
            'pager' =>  $pager,
            'all_module' => $all_module,
            'app'   =>  $app
        ]);

        $this->setTitle('收藏记录');

        return View::fetch();
    }

    /**
     * 设置状态
     */
    public function status()
    {
        $ids = input('ids/a');
        !is_array($ids) && $ids = explode(',', $ids);
        $status = input('status', 0, 'intval');
        $title = '更新';
        if ($status == -1) {
            $title = '删除';
        }
        $data['status'] = $status;

        $res = $this->FavoritesModel->where('id', 'in', $ids)->update($data);
        if ($res) {
            return $this->success($title . '成功', $res, 'refresh');
        } else {
            return $this->error($title . '失败');
        }
    }
}
