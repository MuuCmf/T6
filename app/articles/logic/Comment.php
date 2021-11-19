<?php
namespace app\articles\logic;

use think\facade\Cache;
use app\articles\model\ArticlesConfig as ConfigModel;
use app\articles\logic\Config as ConfigLogic;
use app\articles\model\ArticlesComment as CommentModel;

/*
 * 评论数据逻辑层
 */
class Comment extends Base
{
    protected $ConfigModel;
    protected $ConfigLogic;
    protected $CommentModel;

    public function __construct()
    {
        $this->ConfigModel = new ConfigModel();
        $this->ConfigLogic = new ConfigLogic();
        $this->CommentModel = new CommentModel();
    }

    /**
     * 内容状态
     */
	public $_status = [
        '1'  => '启用',
        '0'  => '禁用',
        '-1' => '未审核',
        '-2' => '审核未通过',
        '-3' => '已删除',
    ];

    /**
     * 条件查询
     * @param  string $keyword       [description]
     * @param  string $category_id   [description]
     * @param  string $attribute_ids [description]
     * @param  string $type          [description]
     * @param  string $status        状态：all:所有 （不包括已删除）1：已上架 0：已下架 -1：未审核 -2：审核未通过 -3：已删除
     * @return [type]                [description]
     */
    public function getMap($shopid, $keyword = '',$article_id = '', $status = 1)
    {
        //初始化查询条件
        $map = [];
        
        if(!empty($shopid)){
            $map[] = ['shopid', '=', $shopid];
        }
        
        if($status == 'all'){
            $map[] = ['status', '>=', -2];
        }elseif($status == 0){
            $map[] = ['status', '>=', $status];
        }else{
            $map[] = ['status', '=', $status];
        }

        if(!empty($keyword)){
            $map[] = ['title', 'like', "%'. $keyword .'%"];
        }
        
        //文章id
        if(!empty($article_id)){
            $map[] = ['article_id', '=', $article_id];
        }

        return $map;
    }

    /**
     * 数据格式化
     */
    public function formatData($data, $shopid = '')
    {
        // 获取店铺配置数据
        $config_data = Cache::get('MUUCMF_ARTICLES_CONFIG_DATA');

        if(!empty($data)){
            $id = $data['id'];
            $data['content'] = htmlspecialchars_decode($data['content']);
            $data['status_str'] = $this->_status[$data['status']];
            if(!empty($data['create_time'])){
                $data['create_time_str'] = time_format($data['create_time']);
                $data['create_time_friendly_str'] = friendly_date($data['create_time']);
            }
            if(!empty($data['update_time'])){
                $data['update_time_str'] = time_format($data['update_time']);
                $data['update_time_friendly_str'] = friendly_date($data['update_time']);
            }
        }

        return $data;
    }

}