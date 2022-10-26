<?php
namespace app\ucenter\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\model\Author as AuthorModel;
use app\common\logic\Author as AuthorLogic;
use app\common\model\AuthorFollow as AuthorFollowModel;
use app\common\logic\AuthorFollow as AuthorFollowLogic;

class Author extends Common
{   
    protected $AuthorModel;
    protected $AuthorLogic;

    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['only'=>['follow','isfollow']],
    ];
    
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

        $this->setTitle($data['name']);
        return View::fetch();
    }

    /**
     * 关注、取消关注
     */
    public function follow()
    {
        $author_id = input('author_id', 0, 'intval');
        $uid = get_uid();

        // 判断是否已关注
        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid],
            ['author_id', '=', $author_id],
        ];
        $follow = (new AuthorFollowModel())->where($map)->find();

        // 写入数据初始化
        $data = [];
        if($follow){
            $data['id'] = $follow['id'];
            if($follow['status'] == 1) $data['status'] = 0;
            if($follow['status'] == 0) $data['status'] = 1;
        }else{
            $data['shopid'] = $this->shopid;
            $data['uid'] = $uid;
            $data['author_id'] = $author_id;
            $data['status'] = 1;
        }

        $res = (new AuthorFollowModel())->edit($data);
        if($res){
            return $this->success($data['status'] ? '已关注':'已取消');
        }

        return $this->error('发生错误');
    }

    /**
     * 是否已关注
     */
    public function isfollow()
    {
        $author_id = input('author_id', 0, 'intval');
        $uid = get_uid();

        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid],
            ['author_id', '=', $author_id],
            ['status', '=', 1]
        ];
        $res = (new AuthorFollowModel())->where($map)->find();
        if($res){
            return $this->success('已关注');
        }

        return $this->error('未关注或缺少参数');
    }

    /**
     * 关注创作者列表
     */
    public function followList()
    {
        $uid = get_uid();
        $rows = input('rows',20, 'intval');
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = $order_field . ' ' . $order_type;

        // 查询条件
        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid],
            ['status', '=', 1]
        ];
        $fields = '*';
        $lists = (new AuthorFollowModel())->getListByPage($map,$order,$fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = (new AuthorFollowLogic())->formatData($val);
        }
        unset($val);
        // ajax请求返回数据
        return $this->success('success', $lists);
    }

}