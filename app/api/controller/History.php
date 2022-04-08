<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: History.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/16
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\History as HistoryModel;
use app\common\logic\History as HistoryLogic;

class History extends Api
{
    protected $model;
    protected $logic;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    function __construct()
    {
        parent::__construct();
        $this->logic = new HistoryLogic();
        $this->model = new HistoryModel();
        //添加jwt中间件
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