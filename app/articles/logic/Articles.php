<?php
namespace app\articles\logic;

use app\articles\model\ArticlesCategory as CategoryModel;
use app\articles\model\ArticlesConfig as ConfigModel;
use app\articles\logic\Config as ConfigLogic;
use app\common\model\Favorites as FavoritesModel;
use app\articles\logic\Favorites as FavoritesLogic;

/*
 * 数据逻辑层
 */
class Articles extends Base
{

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

    protected $ConfigModel;
    protected $ConfigLogic;
    protected $FavoritesModel;

    public function __construct()
    {
        $this->ConfigModel = new ConfigModel();
        $this->ConfigLogic = new ConfigLogic();
        $this->FavoritesModel = new FavoritesModel();
    }

    /**
     * 条件查询
     * @param  string $keyword       [description]
     * @param  string $category_id   [description]
     * @param  string $attribute_ids [description]
     * @param  string $type          [description]
     * @param  string $status        状态：all:所有 （不包括已删除）1：已上架 0：已下架 -1：未审核 -2：审核未通过 -3：已删除
     * @return [type]                [description]
     */
    public function getMap($shopid, $keyword = '',$category_id = '', $status = 1)
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
        
        //分类id
        if(!empty($category_id)){
            $category_ids = $this->CategoryModel->yesParent($category_id); 
            if(!empty($category_ids)){
                $category_ids = implode(',', $category_ids);
                $map[] = ['category_id', 'in', $category_ids];
            }else{
                $map[] = ['category_id', '=', $category_id];
            }
        }

        return $map;
    }

    /**
     * 数据格式化
     */
    public function formatData($data, $shopid = '')
    {
        // 获取店铺配置数据
        $config_data= $this->ConfigModel->getDataByMap(['shopid' => $shopid]);
        $config_data = $this->ConfigLogic->formatData($config_data);
 
        if(!empty($data)){
            $id = $data['id'];
            
            $data = $this->setCoverAttr($data, '4:3');
            
            // 获取分类数据
            if(!empty($data['category'])){
                $data['category'] = $this->CategoryModel->getDataById($data['category_id']);
            }

            $data['content'] = htmlspecialchars_decode($data['content']);
            
            //判断是否收藏
            if($this->FavoritesModel->yesFavorites($shopid, 'articles', get_uid(), $id, 'Articles')){
                $data['favorites_yesno'] = 1;
            }else{
                $data['favorites_yesno'] = 0;
            }
            
            $data['status_str'] = $this->_status[$data['status']];

            if(!empty($data['create_time'])){
                $data['create_time_str'] = time_format($data['create_time']);
                $data['create_time_friendly_str'] = friendly_date($data['create_time']);
            }
            if(!empty($data['update_time'])){
                $data['update_time_str'] = time_format($data['update_time']);
                $data['update_time_friendly_str'] = friendly_date($data['update_time']);
            }

            //拼接url地址
            $data['url'] = url('articles\h5\index',[],'',true) . '#/articles/pages/articles/detail?id='.$data['id'];
        }

        return $data;
    }

}