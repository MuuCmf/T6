<?php
namespace app\ucenter\controller;

use think\facade\View;
use app\common\model\Author as AuthorModel;
use app\common\logic\Author as AuthorLogic;
use app\common\model\AuthorFollow as AuthorFollowModel;
use app\common\logic\AuthorFollow as AuthorFollowLogic;

class Author extends Base
{   
    protected $AuthorModel;
    protected $AuthorLogic;

    public function __construct()
    {
        parent::__construct();
        $this->_initialize();
    }

    public function _initialize()
    {
        $this->AuthorModel   = new AuthorModel();  //模型
        $this->AuthorLogic   = new AuthorLogic();  //逻辑
    }

    /**
     * 作者列表
     * @return     <type>  ( description_of_the_return_value )
     */
    public function lists()
    {
        $rows = input('rows',20, 'intval');
        $keyword = input('keyword','','text');
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = $order_field . ' ' . $order_type;

        // 查询条件
        $map = $this->AuthorLogic->getMap($this->shopid, $keyword, 1);
        $fields = '*';
        $lists = $this->AuthorModel->getListByPage($map,$order,$fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->AuthorLogic->formatData($val);
        }
        unset($val);
        View::assign('pager',$pager);
        View::assign('lists',$lists);
        
        // 设置页面TITLE
        $this->setTitle('创作者列表');
        // 输出模板
        return View::fetch();
    }

    /**
     * 详情
     *
     * @param      integer  $id     The identifier
     * @return     <type>   ( description_of_the_return_value )
     */
    public function detail()
    {
        $id = input('id',0,'intval');

        $data = [];
        if(!empty($id)){
            $data = $this->AuthorModel->getDataById($id);
            $data = $this->AuthorLogic->formatData($data);
        }
        View::assign('data', $data);

        // 查询是否已关注
        $uid = get_uid();
        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid],
            ['author_id', '=', $id],
            ['status', '=', 1]
        ];
        $res = (new AuthorFollowModel())->where($map)->find();
        $has_follow = false;
        if($res){
            $has_follow = true;
        }
        View::assign('has_follow', $has_follow);

        // 设置页面TITLE
        $this->setTitle($data['name']);
        // 输出页面
        return View::fetch();
    }

}