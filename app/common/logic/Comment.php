<?php

namespace app\common\logic;

use app\articles\model\ArticlesComment as CommentModel;
use app\common\model\Support as SupportModel;

/*
 * 评论数据逻辑层
 */

class Comment extends Base
{
    protected $CommentModel;
    protected $SupportModel;

    public function __construct()
    {
        $this->CommentModel = new CommentModel();
        $this->SupportModel = new SupportModel();
    }

    /**
     * 内容状态
     */
    public $_status = [
        1  => '启用',
        0  => '禁用',
        -1 => '未审核',
        -2 => '审核未通过',
        -3 => '已删除',
    ];

    public function getMap($shopid, $keyword = '', $app = '', $info_type = '', $info_id = 0, $status = 1)
    {
        //初始化查询条件
        $map = [];

        if (!empty($shopid)) {
            $map[] = ['shopid', '=', $shopid];
        }

        if (is_numeric($status)) {
            $map[] = ['status', '=', $status];
        }
        if (is_array($status)) {
            $map[] = ['status', 'in', $status];
        }

        if (!empty($keyword)) {
            $map[] = ['content', 'like', '%' . $keyword . '%'];
        }

        if (!empty($app)) {
            $map[] = ['app', '=', $app];
        }
        //内容id
        if (!empty($info_id)) {
            $map[] = ['info_id', '=', $info_id];
        }
        if (!empty($info_type)) {
            $map[] = ['info_type', '=', $info_type];
        }

        return $map;
    }

    /**
     * 数据格式化
     */
    public function formatData($data = [])
    {

        if (!empty($data)) {
            
            $data['info_id'] = (string)$data['info_id'];
            $data['content'] = htmlspecialchars_decode($data['content']);
            $data['status_str'] = $this->_status[$data['status']];
            if (!empty($data['create_time'])) {
                $data['create_time_str'] = time_format($data['create_time']);
                $data['create_time_friendly_str'] = friendly_date($data['create_time']);
            }
            if (!empty($data['update_time'])) {
                $data['update_time_str'] = time_format($data['update_time']);
                $data['update_time_friendly_str'] = friendly_date($data['update_time']);
            }

            $data['user_info'] = query_user($data['uid']);
            //判断是否点赞
            if ($this->SupportModel->yesSupport($data['shopid'], 'articles', get_uid(), $data['id'], 'Comment')) {
                $data['support_yesno'] = 1;
            } else {
                $data['support_yesno'] = 0;
            }
        }

        return $data;
    }
}
