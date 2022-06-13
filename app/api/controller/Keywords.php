<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Keywords as KeywordsModel;
use app\common\logic\Keywords as KeywordsLogic;

class Keywords extends Api
{
    protected $model;
    protected $logic;

    function __construct()
    {
        parent::__construct();
        $this->logic = new KeywordsLogic();
        $this->model = new KeywordsModel();
    }

    /**
     * 用户搜索历史
     */
    public function history()
    {
        $uid = get_uid();
        $params = request()->param();

        
        if(!empty($uid)){
            $map = [
                ['shopid' ,'=' ,$params['shopid']],
                ['status' ,'=' ,1],
                ['uid' ,'=' ,$uid],
            ];

            $rows = $params['rows'] ?? 15;
            $lists = $this->model->getList($map, $rows, 'create_time desc' ,'*');
            foreach ($lists as &$item){
                $item = $this->logic->formatData($item);
            }
            unset($item);

            return $this->success('success',$lists);
        }else{
            return $this->error('用户未登陆');
        }
        
    }

    /**
     * 系统热门（推荐）搜索关键字
     */
    public function hot()
    {
        $params = request()->param();

        $map = [
            ['shopid' ,'=' ,$params['shopid']],
            ['status' ,'=' ,1],
            ['recommend', '=', 1]
        ];

        $rows = $params['rows'] ?? 15;
        $lists = $this->model->getList($map, $rows, 'sort desc,create_time desc' ,'*');
        foreach ($lists as &$item){
            $item = $this->logic->formatData($item);
        }
        unset($item);

        return $this->success('success',$lists);
    }
}