<?php
namespace app\articles\controller\api;

use app\common\model\History;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;

class Articles extends Base
{
    protected $ArticlesModel;
    protected $ArticlesLogic;
    function __construct()
    {
        parent::__construct();
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
    }

    /**
     * 文章列表
     */
    public function lists()
    {
        $keyword = input('keyword', '', 'text');
        $category_id = input('category_id', 0, 'intval');
        $rows = input('rows', 20, 'intval');
        // 约束rows范围
        $rows = min($rows, 100);
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = 'sort DESC,' . $order_field . ' ' . $order_type;

        // 获取查询条件
        $map = $this->ArticlesLogic->getMap($this->shopid, $keyword, $category_id, 1);
        // 获取列表
        $lists = $this->ArticlesModel->getListByPage($map, $order, '*', $rows);
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->ArticlesLogic->formatData($val);
        }
        unset($val);

        return $this->success('success', $lists);
    }

    /**
     * 文章详情
     */
    public function detail()
    {
        $id = input('id', 0, 'intval');
        $uid = get_uid();
        if(!empty($id)){
            $data = $this->ArticlesModel->getDataById($id);
            $data = $this->ArticlesLogic->formatData($data);
            if(!empty($data)){
                //增加浏览数
                $this->ArticlesModel->setStep($id, 'view', 1);
                //写入浏览记录
                if(!empty($uid)){
                    $products = [
                        'title' =>  $data['title'],
                        'desc'  =>  $data['description'],
                        'cover' =>  $data['cover'],
                        //'price' =>  $data['price'],
                        'link_url'   =>  'articles/detail'
                    ];
                    (new History())->addLog($this->shopid, get_module_name(), $uid, $id, 'articles', $products);
                }
                
                return $this->success('success',$data);
            }else{
                return $this->error('error');
            }
        }

        return $this->error('参数错误');
    }
}