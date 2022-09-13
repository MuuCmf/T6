<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Keywords as KeywordsModel;
use app\common\logic\Keywords as KeywordsLogic;

class Keywords extends Api
{
    protected $model;
    protected $logic;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['only' => 'history']
    ];
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

    /**
     * 新增搜索关键字
     */
    public function add()
    {
        $params = input();
        $uid = get_uid();
        // 组装数据
        $data = [
            'uid' => $uid,
            'shopid' => $params['shopid'],
            'keyword' => $params['keyword'],
            'status' => 1
        ];
        // 查询该用户是否查询过
        $has_keyword = $this->model->getDataByMap($data);
        if($has_keyword){
            $data['id'] = $has_keyword['id'];
        }
        // 写入数据
        $result = $this->model->edit($data);

        if($result){
            return $this->success('success', $result);
        }else{
            return $this->error('error');
        }
    }
}