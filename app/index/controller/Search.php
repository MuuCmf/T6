<?php

declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\model\Search as SearchModel;

class Search extends Common
{
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $keyword = input('keyword','','text');
        View::assign('keyword',$keyword);

        // 初始化数据
        $lists = [];
        $pager = '';
        if(!empty($keyword)){
            // 排序方式
            $order_field = input('order_field', 'update_time', 'text');
            View::assign('order_field', $order_field);
            $order_type = input('order_type', 'DESC', 'text');
            View::assign('order_type', $order_type);
            $order = $order_field . ' ' . $order_type;
            // 显示数量
            $rows = input('rows',20, 'intval');
            // 查询条件
            $map = [
                ['shopid', '=', $this->shopid],
                ['content', 'like', '%' . $keyword . '%']
            ];
            $fields = '*';
            $lists = (new SearchModel)->getListByPage($map,$order,$fields, $rows);
            $pager = $lists->render();
            
            $lists = $lists->toArray();
            foreach ($lists['data'] as &$val) {
                $val = (new SearchModel)->handle($val);
                $val['content']['title'] = str_ireplace($keyword, '<strong>' .$keyword. '</strong>', $val['content']['title']);
                $val['content']['description'] = str_ireplace($keyword, '<strong>' .$keyword. '</strong>', $val['content']['description']);
                $val['url'] = url($val['app'] . '/' . $val['info_type'] . '/detail', ['id' => $val['info_id']]);
            }
            unset($val);
            
        }
        View::assign('lists',$lists);
        View::assign('pager',$pager);
        //dump($lists);
        $this->setTitle('搜索');

        return View::fetch();
    }

    /**
     * 搜索弹出框
     */
    public function popup()
    {
        return View::fetch();
    }
}
