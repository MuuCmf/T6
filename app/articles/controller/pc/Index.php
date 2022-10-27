<?php
namespace app\articles\pc\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\model\History;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;

class Index extends Common
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
     * @return [type] [description]
     */
    public function lists()
    {
        $keyword = input('keyword', '', 'text');
        $category_id = input('category_id', 0, 'intval');
        $rows = input('rows', 20, 'intval');
        // 获取查询条件
        $map = $this->ArticlesLogic->getMap($this->shopid, $keyword, $category_id, 1);
        // 获取列表
        $lists = $this->ArticlesModel->getListByPage($map, 'sort DESC,id DESC', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->ArticlesLogic->formatData($val);
        }
        unset($val);

        View::assign('pager', $pager);
        View::assign('lists', $lists);

        return View::fetch();
    }

    /**
     * 文章详情
     */
    public function detail()
    {
        $id = input('id',0,'intval');

        /* 标识正确性检测 */
        if (!($id && is_numeric($id))) {
            return $this->error('文档ID错误！');
        }

        $data = $this->ArticlesModel->getDataById($id);
        $data = $this->ArticlesLogic->formatData($data);
        View::assign('info', $data);
        //未审核内容并不是作者浏览时报错
        if($data['status'] != 1){
            return $this->error('内容审核中...');
        }

        if(!empty($data) && !empty(get_uid())){
            //增加浏览数
            $this->ArticlesModel->setStep($id, 'view', 1);
            //写入浏览记录
            if(!empty($uid)){
                $products = [
                    'title' =>  $data['title'],
                    'desc'  =>  $data['description'],
                    'cover' =>  $data['cover'],
                    'link_url'   =>  'articles/detail'
                ];
                (new History())->addLog($this->shopid, get_module_name(), $uid, $id, 'articles', $products);
            }
        }

        /*用户所要文章访问量*/
        $articles_total_view = $this->ArticlesModel->_totalView($data['author_id']);

        return View::fetch();
    }
    

    private function _category($id=0)
    {
        $category_tree = $this->CategoryModel->tree($this->shopid, 1);
        View::assign('now_category',$category_tree);

        return $category_tree;
    }

    
}
