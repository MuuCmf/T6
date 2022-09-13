<?php
namespace app\articles\logic;

use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;

/*
 * 收藏数据逻辑层
 */
class Favorites
{
    protected $ModuleModel;
    protected $ArticlesModel;
    protected $ArticlesLogic;

    public function __construct()
    {
        $this->ArticlesModel = new ArticlesModel(); //模型
        $this->ArticlesLogic = new ArticlesLogic(); //逻辑
    }

    public function formatData($data)
	{
        $shopid = intval($data['shopid']);

        if(strtolower($data['info_type']) == 'articles'){
            $info = $this->ArticlesModel->getDataById($data['info_id']);
            $info = $this->ArticlesLogic->formatData($info);
        }

        $data['info'] = $info;
        
        // 格式化时间
        if(!empty($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
            $data['create_time_friendly_str'] = friendly_date($data['create_time']);
        }
        if(!empty($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
            $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        }

        return $data;
	}
}