<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Keywords as KeywordsModel;
use app\common\logic\Keywords as KeywordsLogic;

class History extends Api
{
    protected $model;
    protected $logic;

    function __construct()
    {
        parent::__construct();
        $this->logic = new KeywordsLogic();
        $this->model = new KeywordsModel();
    }

    public function lists()
    {
        $uid = request()->uid;
        $params = request()->param();
        $map = [
            ['shopid' ,'=' ,$params['shopid']],
            ['uid' ,'=' ,$uid],
            ['status' ,'=' ,1],
        ];
        $rows = $params['rows'] ?? 15;
        $lists = $this->model->where($map)->page($params['page'],$rows)->order('id','DESC')->select()->toArray();
        foreach ($lists as &$item){
            $item = $this->logic->formatData($item);
        }
        unset($item);
        $this->success('success',$lists);
    }
}