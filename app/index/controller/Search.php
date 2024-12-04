<?php

declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\model\Search as SearchModel;
use app\common\model\Keywords as KeywordsModel;

class Search extends Common
{
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $uid = get_uid();
        $keyword = trim(input('keyword', '', 'text'));
        View::assign('keyword', $keyword);

        // 初始化数据
        $lists = [];
        $pager = '';
        if (!empty($keyword)) {
            // 记录搜索关键字
            $keyword_data = [
                'uid' => $uid,
                'shopid' => $this->shopid,
                'keyword' => $keyword,
                'status' => 1
            ];
            // 查询该用户是否查询过
            $has_keyword = (new KeywordsModel)->getDataByMap($keyword_data);
            if ($has_keyword) {
                $keyword_data['id'] = $has_keyword['id'];
            }
            // 写入数据
            (new KeywordsModel)->edit($keyword_data);

            // 查询数据
            // 排序方式
            $order_field = input('order_field', 'update_time', 'text');
            View::assign('order_field', $order_field);
            $order_type = input('order_type', 'DESC', 'text');
            View::assign('order_type', $order_type);
            $order = $order_field . ' ' . $order_type;
            // 显示数量
            $rows = input('rows', 20, 'intval');
            // 查询条件
            $keyword = preg_replace('/\s+/u', ' ', $keyword); // 将所有空白字符替换为英文空格
            $keyword_arr = preg_split('/\s+/', $keyword, 10, PREG_SPLIT_NO_EMPTY); // 使用英文空格分割字符串
            $keyword_quert_raw = '';
            foreach ($keyword_arr as $key => $val) {
                $keyword_quert_raw .= '`content` LIKE "%' . $val . '%"';
                if ($key < count($keyword_arr) - 1) {
                    $keyword_quert_raw .= ' OR ';
                }
            }
            
            $map = '`shopid` = ' . $this->shopid . ' AND (' . $keyword_quert_raw . ')';
            $fields = '*';
            $lists = (new SearchModel)->getListByPage($map, $order, $fields, $rows);
            $pager = $lists->render();
            $lists = $lists->toArray();

            // 处理搜索结果
            $escapedValues = array_map(function ($value) {
                return preg_quote($value);
            }, $keyword_arr);
            $pattern = '/' . implode('|', $escapedValues) . '/i'; // 创建正则表达式

            foreach ($lists['data'] as &$val) {
                $val = (new SearchModel)->handle($val);
                $val['content']['title'] = preg_replace_callback($pattern, function ($match) {
                    return "<strong>{$match[0]}</strong>"; // 使用引号引起匹配的值
                }, $val['content']['title']);

                $val['content']['description'] = preg_replace_callback($pattern, function ($match) {
                    return "<strong>{$match[0]}</strong>"; // 使用引号引起匹配的值
                }, $val['content']['description']);

                $val['url'] = url($val['app'] . '/' . $val['info_type'] . '/detail', ['id' => $val['info_id']]);
            }
            unset($val);
        }
        View::assign('lists', $lists);
        View::assign('pager', $pager);

        $this->setTitle('搜索');

        return View::fetch();
    }
}
