<?php
namespace app\articles\controller\api;

use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;
use app\common\model\Favorites as FavoritesModel;

class Favorites extends Base
{
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    protected $ArticlesModel;
    protected $ArticlesLogic;
    protected $FavoritesModel;

    public function __construct()
    {
        parent::__construct();
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
        $this->FavoritesModel = new FavoritesModel();
    }

    /**
     * 加入或取消收藏
     */
    public function action()
    {
        if (request()->isPost()){
            $uid = get_uid();
            $info_id = $this->params['info_id'];
            $info_type = $this->params['info_type'];
            if (empty($info_id)){
                return $this->error('数据异常');
            }
            //是否有收藏记录
            $map = [
                ['info_id','=',$info_id],
                ['info_type','=',$info_type],
                ['shopid','=',$this->shopid],
                ['app','=',get_module_name()],
                ['uid','=',$uid]
            ];
            $data = $this->FavoritesModel->getDataByMap($map);
            $data = $data ?? [];
            if (empty($data)){
                //获取元数据
                $products = [];
                $articles = $this->ArticlesModel->where('id',$info_id)->find();
                if ($articles){
                    $articles = $articles->toArray();
                    $products = [
                        'title' =>  $articles['title'],
                        'desc'  =>  $articles['description'],
                        'cover' =>  $articles['cover'],
                        'link_url'   =>  'articles/detail'
                    ];
                }else{
                    return $this->error('文章不存在');
                }

                $f_data = [
                    'shopid' => $this->shopid,
                    'app'    => get_module_name(),
                    'uid'    => $uid,
                    'info_id'   => $info_id,
                    'info_type' => $info_type,
                    'status' => 1,
                    'metadata' =>   json_encode($products)
                ];
            }else{
                $f_data = [
                    'status' => $data['status'] == 1 ? 0 : 1,
                    'id'     => $data['id']
                ];
            }
            $msg_tip = $f_data['status'] == 1 ? '收藏' : '取消';
            $inc_value = $f_data['status'] == 1 ? 1 : -1;

            $result = $this->FavoritesModel->edit($f_data);
            if ($result){
                $this->ArticlesModel->setStep($info_id,'favorites',$inc_value);

                return $this->success($msg_tip . '成功', $f_data['status']);
            }else{
                return $this->error($msg_tip . '失败');
            }
        }

        return $this->error('请求错误');
    }

    /**
     * 查询是否收藏文章
     */
    public function check()
    {
        $uid = get_uid();
        $info_id = $this->params['info_id'];
        $info_type = $this->params['info_type'];
        if (empty($info_id)){
            return $this->error('数据异常');
        }
        //是否有收藏记录
        $map = [
            ['info_id','=',$info_id],
            ['info_type','=',$info_type],
            ['shopid','=',$this->shopid],
            ['app','=',get_module_name()],
            ['uid','=',$uid]
        ];
        $data = $this->FavoritesModel->getDataByMap($map);
        $data = $data ?? [];

        if(empty($data)){
            return $this->error('未收藏');
        }else{
            if($data['status'] == 1){
                return $this->success('已收藏');
            }else{
                return $this->error('未收藏');
            }
        }
    }


}